<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\PostImageStorage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProcessPostImage implements ShouldBeUnique, ShouldQueue, ShouldQueueAfterCommit
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $maxExceptions = 3;

    public int $timeout = 60;

    public bool $failOnTimeout = true;

    public int $uniqueFor = 600;

    public function __construct(
        public readonly int $postId,
        public readonly string $originalImagePath,
    ) {
        $this->onQueue('images');
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('post-image-'.$this->postId))
                ->releaseAfter(5)
                ->expireAfter(90),
        ];
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [5, 15, 30];
    }

    public function uniqueId(): string
    {
        return $this->postId.':'.$this->originalImagePath;
    }

    /**
     * @return array<int, string>
     */
    public function tags(): array
    {
        return ['image-processing', 'post:'.$this->postId];
    }

    public function handle(PostImageStorage $images): void
    {
        $post = Post::query()->find($this->postId);

        // A replacement or deletion makes this queued version obsolete.
        if ($post === null || $post->original_image_path !== $this->originalImagePath) {
            return;
        }

        $originals = Storage::disk(PostImageStorage::ORIGINAL_DISK);
        $processed = Storage::disk(PostImageStorage::PROCESSED_DISK);
        $processedPath = $images->processedPath($this->originalImagePath);

        if ($post->processed_image_path === $processedPath && $processed->exists($processedPath)) {
            return;
        }

        if (! $originals->exists($this->originalImagePath)) {
            throw new RuntimeException('The original post image no longer exists.');
        }

        $storedPath = $originals
            ->image($this->originalImagePath)
            ->orient()
            ->scale(width: 1600)
            ->toWebp()
            ->quality(82)
            ->storePubliclyAs(
                'post-images/processed',
                basename($processedPath),
                PostImageStorage::PROCESSED_DISK,
            );

        if ($storedPath === false) {
            throw new RuntimeException('The processed post image could not be stored.');
        }

        $updated = Post::query()
            ->whereKey($this->postId)
            ->where('original_image_path', $this->originalImagePath)
            ->update(['processed_image_path' => $processedPath]);

        if ($updated === 0 && ! Post::query()
            ->whereKey($this->postId)
            ->where('original_image_path', $this->originalImagePath)
            ->where('processed_image_path', $processedPath)
            ->exists()) {
            $processed->delete($processedPath);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $processedPath = app(PostImageStorage::class)->processedPath($this->originalImagePath);
        $postReferencesOutput = Post::query()
            ->whereKey($this->postId)
            ->where('processed_image_path', $processedPath)
            ->exists();

        if (! $postReferencesOutput) {
            Storage::disk(PostImageStorage::PROCESSED_DISK)->delete($processedPath);
        }
    }
}
