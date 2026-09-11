<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PostImageStorage
{
    public const ORIGINAL_DISK = 'local';

    public const PROCESSED_DISK = 'public';

    public function storeOriginal(UploadedFile $image): string
    {
        $path = $image->store('post-images/originals', self::ORIGINAL_DISK);

        if ($path === false) {
            throw new RuntimeException('The post image could not be stored.');
        }

        return $path;
    }

    public function processedPath(string $originalPath): string
    {
        return 'post-images/processed/'.pathinfo($originalPath, PATHINFO_FILENAME).'.webp';
    }

    public function delete(?string $originalPath, ?string $processedPath): void
    {
        if (filled($originalPath)) {
            Storage::disk(self::ORIGINAL_DISK)->delete($originalPath);
        }

        if (filled($processedPath)) {
            Storage::disk(self::PROCESSED_DISK)->delete($processedPath);
        }
    }
}
