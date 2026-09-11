<?php

namespace Tests\Feature;

use App\Enums\ClaimStage;
use App\Enums\PostStatus;
use App\Jobs\ProcessPostImage;
use App\Models\Post;
use App\Models\User;
use App\Services\PostImageStorage;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostImageTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(PostImageStorage::ORIGINAL_DISK);
        Storage::fake(PostImageStorage::PROCESSED_DISK);
    }

    public function test_a_valid_image_is_stored_privately_and_queued_after_the_post_is_created(): void
    {
        Queue::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('posts.store'), [
            ...$this->validPostData(),
            'image' => UploadedFile::fake()->image('customer-supplied-name.jpg', 1200, 675),
        ])->assertRedirect();

        $post = Post::query()->sole();

        $this->assertNotNull($post->original_image_path);
        $this->assertNull($post->processed_image_path);
        $this->assertStringStartsWith('post-images/originals/', $post->original_image_path);
        $this->assertStringNotContainsString('customer-supplied-name', $post->original_image_path);
        Storage::disk(PostImageStorage::ORIGINAL_DISK)->assertExists($post->original_image_path);
        Storage::disk(PostImageStorage::PROCESSED_DISK)->assertMissing($post->original_image_path);

        Queue::assertPushed(ProcessPostImage::class, function (ProcessPostImage $job) use ($post): bool {
            return $job instanceof ShouldQueueAfterCommit
                && $job->postId === $post->getKey()
                && $job->originalImagePath === $post->original_image_path
                && $job->queue === 'images';
        });
    }

    public function test_image_type_size_and_dimensions_are_validated(): void
    {
        Queue::fake();
        $user = User::factory()->create();

        $invalidImages = [
            UploadedFile::fake()->create('claim.svg', 10, 'image/svg+xml'),
            UploadedFile::fake()->image('too-large.jpg', 1200, 675)->size(8193),
            UploadedFile::fake()->image('too-wide.jpg', 8001, 10),
        ];

        foreach ($invalidImages as $image) {
            $this->actingAs($user)->post(route('posts.store'), [
                ...$this->validPostData(),
                'image' => $image,
            ])->assertSessionHasErrors('image');
        }

        $this->assertDatabaseEmpty('posts');
        Queue::assertNothingPushed();
    }

    public function test_replacing_an_image_cleans_up_old_files_and_queues_the_new_version(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create([
            'original_image_path' => 'post-images/originals/old.jpg',
            'processed_image_path' => 'post-images/processed/old.webp',
        ]);
        Storage::disk(PostImageStorage::ORIGINAL_DISK)->put($post->original_image_path, 'old-original');
        Storage::disk(PostImageStorage::PROCESSED_DISK)->put($post->processed_image_path, 'old-processed');

        $this->actingAs($user)->put(route('posts.update', $post), [
            ...$this->validPostData(),
            'image' => UploadedFile::fake()->image('replacement.png', 1200, 675),
        ])->assertRedirect(route('posts.show', $post));

        $post->refresh();

        $this->assertNotSame('post-images/originals/old.jpg', $post->original_image_path);
        $this->assertNull($post->processed_image_path);
        Storage::disk(PostImageStorage::ORIGINAL_DISK)->assertMissing('post-images/originals/old.jpg');
        Storage::disk(PostImageStorage::PROCESSED_DISK)->assertMissing('post-images/processed/old.webp');
        Storage::disk(PostImageStorage::ORIGINAL_DISK)->assertExists($post->original_image_path);
        Queue::assertPushed(ProcessPostImage::class, fn (ProcessPostImage $job): bool => $job->originalImagePath === $post->original_image_path);
    }

    public function test_an_image_can_be_removed_without_queueing_another_job(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create([
            'original_image_path' => 'post-images/originals/remove.jpg',
            'processed_image_path' => 'post-images/processed/remove.webp',
        ]);
        Storage::disk(PostImageStorage::ORIGINAL_DISK)->put($post->original_image_path, 'original');
        Storage::disk(PostImageStorage::PROCESSED_DISK)->put($post->processed_image_path, 'processed');

        $this->actingAs($user)->put(route('posts.update', $post), [
            ...$this->validPostData(),
            'remove_image' => '1',
        ])->assertRedirect(route('posts.show', $post));

        $post->refresh();

        $this->assertNull($post->original_image_path);
        $this->assertNull($post->processed_image_path);
        Storage::disk(PostImageStorage::ORIGINAL_DISK)->assertMissing('post-images/originals/remove.jpg');
        Storage::disk(PostImageStorage::PROCESSED_DISK)->assertMissing('post-images/processed/remove.webp');
        Queue::assertNothingPushed();
    }

    public function test_updating_without_an_image_keeps_the_existing_files(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create([
            'original_image_path' => 'post-images/originals/keep.jpg',
            'processed_image_path' => 'post-images/processed/keep.webp',
        ]);
        Storage::disk(PostImageStorage::ORIGINAL_DISK)->put($post->original_image_path, 'original');
        Storage::disk(PostImageStorage::PROCESSED_DISK)->put($post->processed_image_path, 'processed');

        $this->actingAs($user)->put(route('posts.update', $post), [
            ...$this->validPostData(),
            'title' => 'The updated assessment experience',
        ])->assertRedirect(route('posts.show', $post));

        $post->refresh();

        $this->assertSame('post-images/originals/keep.jpg', $post->original_image_path);
        $this->assertSame('post-images/processed/keep.webp', $post->processed_image_path);
        Storage::disk(PostImageStorage::ORIGINAL_DISK)->assertExists($post->original_image_path);
        Storage::disk(PostImageStorage::PROCESSED_DISK)->assertExists($post->processed_image_path);
        Queue::assertNothingPushed();
    }

    public function test_an_image_and_removal_request_cannot_be_submitted_together(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $this->actingAs($user)->put(route('posts.update', $post), [
            ...$this->validPostData(),
            'image' => UploadedFile::fake()->image('replacement.jpg'),
            'remove_image' => '1',
        ])->assertSessionHasErrors('remove_image');

        Queue::assertNothingPushed();
    }

    public function test_deleting_a_post_or_its_owner_cleans_up_image_files(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner)->create([
            'original_image_path' => 'post-images/originals/delete.jpg',
            'processed_image_path' => 'post-images/processed/delete.webp',
        ]);
        Storage::disk(PostImageStorage::ORIGINAL_DISK)->put($post->original_image_path, 'original');
        Storage::disk(PostImageStorage::PROCESSED_DISK)->put($post->processed_image_path, 'processed');

        $this->actingAs($owner)
            ->delete(route('posts.destroy', $post))
            ->assertRedirect(route('posts.index'));

        Storage::disk(PostImageStorage::ORIGINAL_DISK)->assertMissing('post-images/originals/delete.jpg');
        Storage::disk(PostImageStorage::PROCESSED_DISK)->assertMissing('post-images/processed/delete.webp');

        $accountOwner = User::factory()->create();
        $accountPost = Post::factory()->for($accountOwner)->create([
            'original_image_path' => 'post-images/originals/account.jpg',
            'processed_image_path' => 'post-images/processed/account.webp',
        ]);
        Storage::disk(PostImageStorage::ORIGINAL_DISK)->put($accountPost->original_image_path, 'original');
        Storage::disk(PostImageStorage::PROCESSED_DISK)->put($accountPost->processed_image_path, 'processed');

        $this->actingAs($accountOwner)->delete(route('profile.destroy'), [
            'password' => 'password',
        ])->assertRedirect(route('home'));

        Storage::disk(PostImageStorage::ORIGINAL_DISK)->assertMissing('post-images/originals/account.jpg');
        Storage::disk(PostImageStorage::PROCESSED_DISK)->assertMissing('post-images/processed/account.webp');
    }

    public function test_the_job_creates_a_webp_derivative_and_is_idempotent(): void
    {
        $post = Post::factory()->create();
        $originalPath = UploadedFile::fake()
            ->image('source.png', 2000, 1000)
            ->storeAs('post-images/originals', 'source.png', PostImageStorage::ORIGINAL_DISK);
        $post->original_image_path = $originalPath;
        $post->save();

        $job = new ProcessPostImage($post->getKey(), $originalPath);
        $job->handle(app(PostImageStorage::class));
        $firstProcessedPath = $post->fresh()->processed_image_path;

        $this->assertSame('post-images/processed/source.webp', $firstProcessedPath);
        Storage::disk(PostImageStorage::PROCESSED_DISK)->assertExists($firstProcessedPath);
        $this->assertSame(
            'image/webp',
            Storage::disk(PostImageStorage::PROCESSED_DISK)->mimeType($firstProcessedPath),
        );

        [$width, $height] = getimagesizefromstring(
            Storage::disk(PostImageStorage::PROCESSED_DISK)->get($firstProcessedPath),
        );
        $this->assertSame(1600, $width);
        $this->assertSame(800, $height);

        $job->handle(app(PostImageStorage::class));

        $this->assertSame($firstProcessedPath, $post->fresh()->processed_image_path);
        Storage::disk(PostImageStorage::PROCESSED_DISK)->assertExists($firstProcessedPath);
    }

    public function test_the_job_does_not_upscale_a_smaller_image(): void
    {
        $post = Post::factory()->create();
        $originalPath = UploadedFile::fake()
            ->image('small.png', 800, 400)
            ->storeAs('post-images/originals', 'small.png', PostImageStorage::ORIGINAL_DISK);
        $post->original_image_path = $originalPath;
        $post->save();

        (new ProcessPostImage($post->getKey(), $originalPath))
            ->handle(app(PostImageStorage::class));

        $processedPath = $post->fresh()->processed_image_path;
        [$width, $height] = getimagesizefromstring(
            Storage::disk(PostImageStorage::PROCESSED_DISK)->get($processedPath),
        );

        $this->assertSame(800, $width);
        $this->assertSame(400, $height);
    }

    public function test_a_stale_job_does_not_overwrite_a_replacement_image(): void
    {
        $post = Post::factory()->create([
            'original_image_path' => 'post-images/originals/replacement.png',
        ]);
        Storage::disk(PostImageStorage::ORIGINAL_DISK)->put(
            'post-images/originals/obsolete.png',
            UploadedFile::fake()->image('obsolete.png')->getContent(),
        );

        (new ProcessPostImage(
            $post->getKey(),
            'post-images/originals/obsolete.png',
        ))->handle(app(PostImageStorage::class));

        $this->assertNull($post->fresh()->processed_image_path);
        Storage::disk(PostImageStorage::PROCESSED_DISK)
            ->assertMissing('post-images/processed/obsolete.webp');
    }

    public function test_the_job_has_bounded_retries_and_cleans_up_an_unreferenced_failed_output(): void
    {
        $post = Post::factory()->create([
            'original_image_path' => 'post-images/originals/failure.png',
        ]);
        $job = new ProcessPostImage($post->getKey(), $post->original_image_path);
        $processedPath = 'post-images/processed/failure.webp';
        Storage::disk(PostImageStorage::PROCESSED_DISK)->put($processedPath, 'partial-output');

        $this->assertSame(5, $job->tries);
        $this->assertSame(3, $job->maxExceptions);
        $this->assertSame(60, $job->timeout);
        $this->assertTrue($job->failOnTimeout);
        $this->assertSame([5, 15, 30], $job->backoff());

        $job->failed(null);

        Storage::disk(PostImageStorage::PROCESSED_DISK)->assertMissing($processedPath);
    }

    /**
     * @return array{title: string, content: string, claim_stage: string, status: string}
     */
    private function validPostData(): array
    {
        return [
            'title' => 'A clear assessment experience',
            'content' => 'This claim experience explains the process clearly without including identifying information.',
            'claim_stage' => ClaimStage::Assessment->value,
            'status' => PostStatus::Published->value,
        ];
    }
}
