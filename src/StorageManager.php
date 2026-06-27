<?php

namespace DaiyanMozumder\ImageWizard;

use DaiyanMozumder\ImageWizard\Exceptions\ImageWizardException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageManager
{
    /**
     * Download a file from a Laravel disk to a local temporary path.
     *
     * @param string $disk
     * @param string $path
     * @return string Local temporary file path
     * @throws ImageWizardException
     */
    public function downloadToTemp(string $disk, string $path): string
    {
        $storage = Storage::disk($disk);
        
        if (!$storage->exists($path)) {
            throw new ImageWizardException("File not found on disk [{$disk}]: {$path}");
        }

        $tempDir = storage_path('app/temp/image-wizard');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir . DIRECTORY_SEPARATOR . Str::random(40) . '-' . basename($path);
        
        $stream = $storage->readStream($path);
        $tempFile = fopen($tempPath, 'w');
        stream_copy_to_stream($stream, $tempFile);
        fclose($tempFile);
        fclose($stream);

        return $tempPath;
    }

    /**
     * Upload a local temporary file to a Laravel disk.
     *
     * @param string $localPath
     * @param string $disk
     * @param string $destinationPath
     * @return bool
     */
    public function uploadFromTemp(string $localPath, string $disk, string $destinationPath): bool
    {
        if (!file_exists($localPath)) {
            return false;
        }

        $storage = Storage::disk($disk);
        $stream = fopen($localPath, 'r');
        $result = $storage->writeStream($destinationPath, $stream);
        fclose($stream);

        return $result;
    }

    /**
     * Delete a local file.
     *
     * @param string $path
     * @return void
     */
    public function cleanup(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }
    
    /**
     * Generate a safe temporary file path.
     * 
     * @param string $extension
     * @return string
     */
    public function generateTempPath(string $extension = ''): string
    {
        $tempDir = storage_path('app/temp/image-wizard');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        $ext = $extension ? '.' . ltrim($extension, '.') : '';
        return $tempDir . DIRECTORY_SEPARATOR . Str::random(40) . $ext;
    }
}
