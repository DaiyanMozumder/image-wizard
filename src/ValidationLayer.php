<?php

namespace DaiyanMozumder\ImageWizard;

use DaiyanMozumder\ImageWizard\Exceptions\ImageWizardException;

class ValidationLayer
{
    protected array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/avif',
        'image/gif',
        'image/bmp',
        'image/tiff'
    ];

    /**
     * Validate the source image before processing.
     *
     * @param string $path
     * @return void
     * @throws ImageWizardException
     */
    public function validateSource(string $path): void
    {
        if (!file_exists($path)) {
            throw new ImageWizardException("Source image does not exist: {$path}");
        }

        if (!is_readable($path)) {
            throw new ImageWizardException("Source image is not readable: {$path}");
        }

        // Check if file is empty
        if (filesize($path) === 0) {
            throw new ImageWizardException("Source image is empty: {$path}");
        }

        $mimeType = mime_content_type($path);
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            throw new ImageWizardException("Unsupported image format: {$mimeType}");
        }
    }
}
