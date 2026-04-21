<?php

namespace Modules\Tool\Services\Database;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseService implements DatabaseServiceInterface
{
    /** @inheritDoc */
    public function index(): array
    {
        return [
            'backups' => $this->getBackups(),
        ];
    }

    /**
     * Get all backup files sorted latest first.
     */
    private function getBackups(): array
    {
        $directory = $this->backupDirectory();

        if (!File::isDirectory($directory)) {
            return [];
        }

        return collect(File::files($directory))
            ->filter(fn($file) => Str::endsWith($file->getFilename(), '.sql'))
            ->sortByDesc(fn($file) => $file->getMTime())
            ->values()
            ->map(fn($file) => $this->backupMeta($file->getPathname()))
            ->all();
    }

    /**
     * Get backup storage directory.
     */
    private function backupDirectory(): string
    {
        return storage_path('app/backups/database');
    }

    /**
     * Build backup metadata array.
     */
    private function backupMeta(string $path): array
    {
        $timestamp = File::lastModified($path);

        return [
            'name' => basename($path),
            'size' => File::size($path),
            'size_human' => humanFileSize(File::size($path)),
            'created_at' => dateTimeFormat(Carbon::createFromTimestamp($timestamp)),
        ];
    }

    /** @inheritDoc */
    public function backup(): array
    {
        $config = $this->getDatabaseConfig();
        $directory = $this->backupDirectory();
        File::ensureDirectoryExists($directory);

        $filename = sprintf('backup_%s_%s.sql', $config['database'], now()->format('Y_m_d_His'));
        $filePath = $directory . DIRECTORY_SEPARATOR . $filename;

        if ($config['driver'] === 'mysql') {
            $dumpBinary = $this->findBinary([
                env('DB_DUMP_BINARY'),
                'mysqldump',
                'mariadb-dump',
            ]);

            if ($dumpBinary) {
                $process = new Process([
                    $dumpBinary,
                    '--host=' . $config['host'],
                    '--port=' . $config['port'],
                    '--user=' . $config['username'],
                    '--single-transaction',
                    '--skip-lock-tables',
                    '--databases',
                    $config['database'],
                    '--result-file=' . $filePath,
                ]);

                $this->runProcess($process, $config);
            } else {
                $this->createMysqlBackupInternally($config['database'], $filePath);
            }
        } elseif ($config['driver'] === 'pgsql') {
            $dumpBinary = $this->findBinary([
                env('DB_DUMP_BINARY'),
                'pg_dump',
            ]);

            if (!$dumpBinary) {
                throw new RuntimeException('PostgreSQL backup requires pg_dump to be installed or configured in DB_DUMP_BINARY.');
            }

            $process = new Process([
                $dumpBinary,
                '--host=' . $config['host'],
                '--port=' . $config['port'],
                '--username=' . $config['username'],
                '--dbname=' . $config['database'],
                '--clean',
                '--if-exists',
                '--no-owner',
                '--no-privileges',
                '--format=plain',
                '--file=' . $filePath,
            ]);

            $this->runProcess($process, $config);
        } else {
            throw new RuntimeException('Database backup is not supported for the active driver.');
        }

        $backup = $this->backupMeta($filePath);

        $this->logAction('backup', [
            'engine' => $dumpBinary ? 'binary' : 'internal',
            'driver' => $config['driver'],
            'backup' => $backup,
        ]);

        return [
            'backup' => $backup,
            'backups' => $this->getBackups(),
        ];
    }

    /**
     * Get active database connection config.
     */
    private function getDatabaseConfig(): array
    {
        $connection = config('database.default');
        $config = config("database.connections.$connection");

        if (!is_array($config)) {
            throw new RuntimeException('Database connection configuration is invalid.');
        }

        return [
            'driver' => (string)($config['driver'] ?? ''),
            'host' => (string)($config['host'] ?? '127.0.0.1'),
            'port' => (string)($config['port'] ?? (($config['driver'] ?? null) === 'pgsql' ? '5432' : '3306')),
            'database' => (string)($config['database'] ?? ''),
            'username' => (string)($config['username'] ?? ''),
            'password' => (string)($config['password'] ?? ''),
        ];
    }

    /**
     * Locate the first available binary.
     *
     * @param array<int, string|null> $binaries
     */
    private function findBinary(array $binaries): ?string
    {
        foreach ($binaries as $binary) {
            if (blank($binary)) {
                continue;
            }

            $process = new Process(['sh', '-lc', 'command -v ' . escapeshellarg((string)$binary)]);
            $process->run();
            if ($process->isSuccessful()) {
                return (string)$binary;
            }
        }

        return null;
    }

    /**
     * Run a process and throw domain-friendly exception when failed.
     */
    private function runProcess(Process $process, array $config): void
    {
        $env = [];

        if ($config['driver'] === 'mysql') {
            $env['MYSQL_PWD'] = $config['password'];
        } elseif ($config['driver'] === 'pgsql') {
            $env['PGPASSWORD'] = $config['password'];
        }

        $process->setEnv($env);

        $process->setTimeout(1800);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            throw new RuntimeException($exception->getProcess()->getErrorOutput() ?: $exception->getMessage());
        }
    }

    /**
     * Fallback backup when mysqldump is unavailable.
     */
    private function createMysqlBackupInternally(string $database, string $filePath): void
    {
        $pdo = DB::connection()->getPdo();
        $rawTables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
        $dump = [];
        $dump[] = '-- Forkiva SQL Backup';
        $dump[] = '-- Database: ' . $database;
        $dump[] = '-- Generated at: ' . now()->toDateTimeString();
        $dump[] = 'SET FOREIGN_KEY_CHECKS=0;';
        $dump[] = '';

        foreach ($rawTables as $tableRow) {
            $values = array_values((array)$tableRow);
            $table = (string)($values[0] ?? '');
            if ($table === '') {
                continue;
            }

            $createTableRow = DB::selectOne('SHOW CREATE TABLE `' . str_replace('`', '``', $table) . '`');
            $createValues = array_values((array)$createTableRow);
            $createSql = (string)($createValues[1] ?? '');

            $dump[] = '--';
            $dump[] = '-- Table structure for table `' . $table . '`';
            $dump[] = '--';
            $dump[] = 'DROP TABLE IF EXISTS `' . str_replace('`', '``', $table) . '`;';
            $dump[] = $createSql . ';';
            $dump[] = '';

            $rows = DB::table($table)->get();
            if ($rows->isEmpty()) {
                continue;
            }

            $dump[] = '--';
            $dump[] = '-- Data for table `' . $table . '`';
            $dump[] = '--';

            foreach ($rows as $row) {
                $arrayRow = (array)$row;
                $columns = implode(', ', array_map(
                    fn($column) => '`' . str_replace('`', '``', (string)$column) . '`',
                    array_keys($arrayRow)
                ));

                $valuesSql = implode(', ', array_map(
                    function ($value) use ($pdo) {
                        if ($value === null) {
                            return 'NULL';
                        }

                        if (is_bool($value)) {
                            return $value ? '1' : '0';
                        }

                        if (is_int($value) || is_float($value)) {
                            return (string)$value;
                        }

                        return $pdo->quote((string)$value) ?: "''";
                    },
                    array_values($arrayRow)
                ));

                $dump[] = 'INSERT INTO `' . str_replace('`', '``', $table) . '` (' . $columns . ') VALUES (' . $valuesSql . ');';
            }

            $dump[] = '';
        }

        $dump[] = 'SET FOREIGN_KEY_CHECKS=1;';
        File::put($filePath, implode(PHP_EOL, $dump) . PHP_EOL);
    }

    /**
     * Store custom activity log for backup/restore operations.
     */
    private function logAction(string $event, array $details = []): void
    {
        $activity = new Activity();

        if (Auth::check()) {
            $activity->causer_id = Auth::id();
            $activity->causer_type = get_class(Auth::user());
        }

        $activity->log_name = 'database_tools';
        $activity->event = $event;
        $activity->description = 'tool::database.database';
        $activity->batch_uuid = (string)Str::uuid();
        $activity->properties = [
            'details' => $details,
            'info' => [
                'ip' => request()->ip(),
                'http_method' => request()->method(),
                'user_agent' => request()->header('User-Agent'),
            ],
            'trans_params' => [
                'resource' => __('tool::database.database'),
            ],
        ];

        $activity->save();
    }

    /** @inheritDoc */
    public function restore(UploadedFile $file): array
    {
        $config = $this->getDatabaseConfig();
        $restoreBinary = $this->restoreUsingConfiguredDriver($config, File::get($file->getRealPath()));

        $this->logAction('restored', [
            'engine' => $restoreBinary ? 'binary' : 'internal',
            'driver' => $config['driver'],
            'file_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);

        return [
            'restored_file' => $file->getClientOriginalName(),
        ];
    }

    /**
     * Fallback restore when mysql client is unavailable.
     */
    private function restoreInternally(string $sql): void
    {
        $config = $this->getDatabaseConfig();

        if ($config['driver'] === 'mysql') {
            DB::unprepared('SET FOREIGN_KEY_CHECKS=0;');
        }

        foreach ($this->splitSqlStatements($sql) as $statement) {
            $trimmed = trim($statement);
            if ($trimmed === '') {
                continue;
            }

            DB::unprepared($trimmed);
        }

        if ($config['driver'] === 'mysql') {
            DB::unprepared('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Split SQL content into executable statements.
     *
     * @return array<int, string>
     */
    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $inBacktick = false;
        $inLineComment = false;
        $inBlockComment = false;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : '';

            if ($inLineComment) {
                $buffer .= $char;
                if ($char === "\n") {
                    $inLineComment = false;
                }
                continue;
            }

            if ($inBlockComment) {
                $buffer .= $char;
                if ($char === '*' && $next === '/') {
                    $buffer .= '/';
                    $i++;
                    $inBlockComment = false;
                }
                continue;
            }

            if (!$inSingleQuote && !$inDoubleQuote && !$inBacktick) {
                if ($char === '-' && $next === '-') {
                    $inLineComment = true;
                    $buffer .= $char;
                    continue;
                }
                if ($char === '#') {
                    $inLineComment = true;
                    $buffer .= $char;
                    continue;
                }
                if ($char === '/' && $next === '*') {
                    $inBlockComment = true;
                    $buffer .= $char;
                    continue;
                }
            }

            if ($char === "'" && !$inDoubleQuote && !$inBacktick) {
                $prev = $i > 0 ? $sql[$i - 1] : '';
                if ($prev !== '\\') {
                    $inSingleQuote = !$inSingleQuote;
                }
            } elseif ($char === '"' && !$inSingleQuote && !$inBacktick) {
                $prev = $i > 0 ? $sql[$i - 1] : '';
                if ($prev !== '\\') {
                    $inDoubleQuote = !$inDoubleQuote;
                }
            } elseif ($char === '`' && !$inSingleQuote && !$inDoubleQuote) {
                $inBacktick = !$inBacktick;
            }

            if ($char === ';' && !$inSingleQuote && !$inDoubleQuote && !$inBacktick) {
                $statements[] = $buffer;
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        if (trim($buffer) !== '') {
            $statements[] = $buffer;
        }

        return $statements;
    }

    /** @inheritDoc */
    public function restoreFromBackup(string $fileName): array
    {
        $path = $this->resolveBackupPath($fileName);
        if (!File::exists($path)) {
            throw new RuntimeException(__('tool::database.messages.backup_not_found'));
        }

        $config = $this->getDatabaseConfig();
        $restoreBinary = $this->restoreUsingConfiguredDriver($config, File::get($path));

        $this->logAction('restored', [
            'engine' => $restoreBinary ? 'binary' : 'internal',
            'driver' => $config['driver'],
            'file_name' => basename($path),
            'size' => File::size($path),
            'from_backup' => true,
        ]);

        return [
            'restored_file' => basename($path),
        ];
    }

    /**
     * Resolve backup file path securely.
     */
    private function resolveBackupPath(string $fileName): string
    {
        $name = basename($fileName);
        if (!preg_match('/^[A-Za-z0-9._-]+\\.sql$/', $name)) {
            throw new RuntimeException(__('tool::database.messages.invalid_backup_file'));
        }

        return $this->backupDirectory() . DIRECTORY_SEPARATOR . $name;
    }

    /** @inheritDoc */
    public function download(string $fileName): BinaryFileResponse
    {
        $path = $this->resolveBackupPath($fileName);
        if (!File::exists($path)) {
            throw new RuntimeException(__('tool::database.messages.backup_not_found'));
        }

        $this->logAction('download', [
            'file_name' => basename($path),
            'size' => File::size($path),
        ]);

        return response()->download($path, basename($path));
    }

    /**
     * Restore SQL using the active database driver.
     */
    private function restoreUsingConfiguredDriver(array $config, string $sql): ?string
    {
        if ($config['driver'] === 'mysql') {
            $restoreBinary = $this->findBinary([env('DB_RESTORE_BINARY'), 'mysql', 'mariadb']);

            if ($restoreBinary) {
                $process = new Process([
                    $restoreBinary,
                    '--host=' . $config['host'],
                    '--port=' . $config['port'],
                    '--user=' . $config['username'],
                    $config['database'],
                ]);

                $process->setInput($sql);
                $this->runProcess($process, $config);

                return $restoreBinary;
            }

            $this->restoreInternally($sql);

            return null;
        }

        if ($config['driver'] === 'pgsql') {
            $restoreBinary = $this->findBinary([env('DB_RESTORE_BINARY'), 'psql']);

            if ($restoreBinary) {
                $process = new Process([
                    $restoreBinary,
                    '--host=' . $config['host'],
                    '--port=' . $config['port'],
                    '--username=' . $config['username'],
                    '--dbname=' . $config['database'],
                    '--set=ON_ERROR_STOP=on',
                ]);

                $process->setInput($sql);
                $this->runProcess($process, $config);

                return $restoreBinary;
            }

            $this->restoreInternally($sql);

            return null;
        }

        throw new RuntimeException('Database restore is not supported for the active driver.');
    }
}
