<?php

namespace Modules\Tool\Services\Database;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface DatabaseServiceInterface
{
    /**
     * Get database tool meta.
     *
     * @return array
     */
    public function index(): array;

    /**
     * Create database backup.
     *
     * @return array
     */
    public function backup(): array;

    /**
     * Restore database from uploaded SQL file.
     *
     * @param UploadedFile $file
     * @return array
     */
    public function restore(UploadedFile $file): array;

    /**
     * Restore database from existing backup file name.
     *
     * @param string $fileName
     * @return array
     * @throws FileNotFoundException
     */
    public function restoreFromBackup(string $fileName): array;

    /**
     * Download backup file.
     *
     * @param string $fileName
     * @return BinaryFileResponse
     */
    public function download(string $fileName): BinaryFileResponse;
}
