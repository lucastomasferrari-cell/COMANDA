<?php

namespace Modules\Installer\Http\Controllers;

use Artisan;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Modules\Core\Http\Controllers\Controller;
use Modules\User\Models\User;

class InstallerController extends Controller
{
    private string $tracker;

    public function __construct()
    {
        $this->tracker = storage_path('framework/install.json');
    }

    /**
     * Welcome step
     * @throws FileNotFoundException
     */
    public function welcome(): View
    {
        File::delete($this->tracker);
        session()->forget('installer');

        $this->markStep('welcome');

        return view('installer.welcome');
    }

    /**
     * @throws FileNotFoundException
     */
    private function markStep(string $step): void
    {
        $data = $this->getSteps();
        $data[$step] = true;

        File::put($this->tracker, json_encode($data));
        session()->put("installer.$step", true);
        session()->save();
    }

    /**
     * @throws FileNotFoundException
     */
    private function getSteps(): array
    {
        if (File::exists($this->tracker)) {
            return json_decode(File::get($this->tracker), true) ?? [];
        }
        return [];
    }

    /**
     * System requirements check
     * @throws FileNotFoundException
     */
    public function requirements(): View|RedirectResponse
    {
        if (!$this->stepDone('welcome')) {
            return redirect()->route('installer.welcome');
        }

        $nodeCheck = $this->detectNodeInstallation();


        $requirements = [
            'PHP >= 8.3' => version_compare(PHP_VERSION, '8.3.0', '>='),
            'ctype' => extension_loaded('ctype'),
            'curl' => extension_loaded('curl'),
            'dom' => extension_loaded('dom'),
            'fileinfo' => extension_loaded('fileinfo'),
            'filter' => extension_loaded('filter'),
            'hash' => extension_loaded('hash'),
            'mbstring' => extension_loaded('mbstring'),
            'openssl' => extension_loaded('openssl'),
            'pcre' => extension_loaded('pcre'),
            'pdo' => extension_loaded('pdo'),
            'session' => extension_loaded('session'),
            'tokenizer' => extension_loaded('tokenizer'),
            'xml' => extension_loaded('xml'),
            'intl' => extension_loaded('intl'),
            'gd' => extension_loaded('gd'),

            'Node.js >= 18' => $nodeCheck['node'] !== 'unknown',
            'npm installed' => $nodeCheck['npm'] !== 'unknown',
            'Puppeteer installed' => $nodeCheck['puppeteer'] !== 'unknown',
        ];

        if (!function_exists('shell_exec')) {
            session()->flash(
                'error',
                'This server does not allow command execution (shell_exec disabled). Node.js and Puppeteer cannot run here.'
            );
        }

        $this->markStep('requirements');

        $listNotExtensions = [
            'PHP >= 8.3',
            'Node.js >= 18',
            'npm installed',
            'Puppeteer installed',
        ];

        return view('installer.requirements', compact(
            'requirements',
            'listNotExtensions'
        ));
    }

    /**
     * @throws FileNotFoundException
     */
    private function stepDone(string $step): bool
    {
        return $this->getSteps()[$step] ?? false;
    }

    private function detectNodeInstallation(): array
    {
        $result = [
            'node' => 'unknown',
            'npm' => 'unknown',
            'puppeteer' => 'unknown',
        ];


        if (getenv('NODE_VERSION')) {
            $result['node'] = 'likely';
        }

        $paths = PHP_OS_FAMILY === 'Windows'
            ? [
                'C:\\Program Files\\nodejs\\node.exe',
                'C:\\Program Files (x86)\\nodejs\\node.exe',
            ]
            : [
                '/usr/bin/node',
                '/usr/local/bin/node',
                '/opt/homebrew/bin/node',
                '/snap/bin/node',
            ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                $result['node'] = 'installed';
                break;
            }
        }

        $npmPaths = PHP_OS_FAMILY === 'Windows'
            ? [
                'C:\\Program Files\\nodejs\\npm.cmd',
            ]
            : [
                '/usr/bin/npm',
                '/usr/local/bin/npm',
                '/opt/homebrew/bin/npm',
            ];

        foreach ($npmPaths as $path) {
            if (is_file($path)) {
                $result['npm'] = 'installed';
                break;
            }
        }

        $possibleNodeModules = [
            base_path('node_modules/puppeteer'),
            base_path('node_modules/puppeteer-core'),
        ];

        foreach ($possibleNodeModules as $dir) {
            if (is_dir($dir)) {
                $result['puppeteer'] = 'installed';
                break;
            }
        }

        return $result;
    }

    /**
     * Permissions check
     * @throws FileNotFoundException
     */
    public function permissions(): View|RedirectResponse
    {
        if (!$this->stepDone('requirements')) {
            return redirect()->route('installer.requirements');
        }

        $paths = [
            '.env' => base_path('.env'),
            'storage' => storage_path(),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        $permissions = array_map(function ($path) {
            return is_writable($path);
        }, $paths);

        $this->markStep('permissions');

        return view('installer.permissions', compact('permissions'));
    }

    /**
     * Database setup
     * @throws FileNotFoundException
     */
    public function database(Request $request): View|RedirectResponse
    {
        if (!$this->stepDone('permissions')) {
            return redirect()->route('installer.permissions');
        }

        $connections = [
            'mysql' => 'MySQL',
            'pgsql' => 'PostgreSQL',
        ];

        if ($request->isMethod('post')) {

            $data = $request->validate([
                'db_connection' => ['required', Rule::in(array_keys($connections))],
                'db_host' => 'required|string',
                'db_port' => 'required|numeric',
                'db_name' => 'required|string',
                'db_user' => 'required|string',
                'db_pass' => 'nullable|string',
            ]);

            if (!File::exists(base_path('.env'))) {
                File::copy(base_path('.env.example'), base_path('.env'));
            }

            DotenvEditor::setKeys([
                'DB_CONNECTION' => $data['db_connection'],
                'DB_HOST' => $data['db_host'],
                'DB_PORT' => $data['db_port'],
                'DB_DATABASE' => $data['db_name'],
                'DB_USERNAME' => $data['db_user'],
                'DB_PASSWORD' => $data['db_pass'],
                'APP_SEED_DEMO_DATA' => $request->with_demo ? 'true' : 'false',
            ])->save();

            config([
                'database.default' => $data['db_connection'],
                "database.connections.{$data['db_connection']}.host" => $data['db_host'],
                "database.connections.{$data['db_connection']}.port" => $data['db_port'],
                "database.connections.{$data['db_connection']}.database" => $data['db_name'],
                "database.connections.{$data['db_connection']}.username" => $data['db_user'],
                "database.connections.{$data['db_connection']}.password" => $data['db_pass'],
                'app.seed_demo_data' => (bool)$request->with_demo,
            ]);

            DB::purge();
            DB::reconnect();

            try {
                DB::connection()->getPdo();
            } catch (Exception $e) {
                return back()->withErrors([
                    'db_error' => 'Database connection failed: ' . $e->getMessage()
                ]);
            }

            Artisan::call('key:generate', ['--force' => true]);
            Artisan::call('module:migrate', ['-a' => true, '--force' => true]);
            Artisan::call('module:seed', ['-a' => true, '--force' => true]);

            $this->markStep('database');

            return redirect()->route('installer.admin');
        }

        return view('installer.database', compact('connections'));
    }

    /**
     * Admin user setup
     * @throws FileNotFoundException
     */
    public function admin(Request $request): View|RedirectResponse
    {
        if (!$this->stepDone('database')) {
            return redirect()->route('installer.database');
        }

        if ($request->isMethod('post')) {

            $data = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => [
                    'required',
                    'string',
                    Password::min(8)->max(20)->mixedCase()->numbers()->symbols(),
                    'confirmed',
                ],
            ]);

            User::query()
                ->where('id', 1)->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'username' => explode('@', $data['email'])[0],
                    'password' => bcrypt($data['password']),
                ]);

            $this->markStep('admin');

            return redirect()->route('installer.finish');
        }

        return view('installer.admin');
    }

    /**
     * Finish installer
     * @throws FileNotFoundException
     */
    public function finish(): View|RedirectResponse
    {
        if (!$this->stepDone('admin')) {
            return redirect()->route('installer.admin');
        }

        DotenvEditor::setKeys([
            'APP_INSTALLED' => 'true',
            'APP_URL' => request()->getSchemeAndHttpHost(),
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
        ])->save();

        Artisan::call('storage:link');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');

        File::delete($this->tracker);
        session()->forget('installer');

        return view('installer.finish');
    }


}
