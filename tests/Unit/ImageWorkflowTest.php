<?php

namespace Tests\Unit;

use App\Jobs\ProcessPostImage;
use App\Services\PostImageStorage;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageWorkflowTest extends TestCase
{
    public function test_processed_paths_and_job_identity_are_deterministic(): void
    {
        $images = new PostImageStorage;
        $originalPath = 'post-images/originals/source-photo.png';
        $job = new ProcessPostImage(42, $originalPath);

        $this->assertSame('post-images/processed/source-photo.webp', $images->processedPath($originalPath));
        $this->assertSame('42:'.$originalPath, $job->uniqueId());
        $this->assertSame(['image-processing', 'post:42'], $job->tags());
        $this->assertSame('images', $job->queue);
    }

    public function test_deleting_an_image_removes_only_the_given_paths(): void
    {
        Storage::fake(PostImageStorage::ORIGINAL_DISK);
        Storage::fake(PostImageStorage::PROCESSED_DISK);
        $images = new PostImageStorage;
        $originalPath = 'post-images/originals/source.png';
        $processedPath = 'post-images/processed/source.webp';
        $unrelatedPath = 'post-images/processed/unrelated.webp';

        Storage::disk(PostImageStorage::ORIGINAL_DISK)->put($originalPath, 'source');
        Storage::disk(PostImageStorage::PROCESSED_DISK)->put($processedPath, 'processed');
        Storage::disk(PostImageStorage::PROCESSED_DISK)->put($unrelatedPath, 'other');

        $images->delete($originalPath, $processedPath);
        $images->delete(null, null);

        Storage::disk(PostImageStorage::ORIGINAL_DISK)->assertMissing($originalPath);
        Storage::disk(PostImageStorage::PROCESSED_DISK)->assertMissing($processedPath);
        Storage::disk(PostImageStorage::PROCESSED_DISK)->assertExists($unrelatedPath);
    }
}
