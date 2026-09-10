<?php

namespace Database\Seeders;

use App\Enums\ClaimStage;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'author' => 'kronos.p@example.com',
                'slug' => 'preparing-for-the-first-assessment',
                'title' => 'Preparing for the first assessment conversation',
                'claim_stage' => ClaimStage::Assessment,
                'status' => PostStatus::Published,
                'published_at' => now()->subDays(8),
                'content' => 'I found it useful to prepare a short timeline before the assessment conversation. I kept it general, removed every identifying reference, and focused on the sequence of events and the questions I still needed answered.',
            ],
            [
                'author' => 'themis.r@example.com',
                'slug' => 'questions-to-ask-during-review',
                'title' => 'Questions that helped during the review stage',
                'claim_stage' => ClaimStage::Review,
                'status' => PostStatus::Published,
                'published_at' => now()->subDays(5),
                'content' => 'During review, written questions helped me understand which documents were still needed and when I should expect the next update. Sharing the process without personal identifiers can help others prepare for similar conversations.',
            ],
            [
                'author' => 'atlas.m@example.com',
                'slug' => 'understanding-a-claim-decision',
                'title' => 'Understanding the wording of a claim decision',
                'claim_stage' => ClaimStage::Decision,
                'status' => PostStatus::Published,
                'published_at' => now()->subDays(2),
                'content' => 'A clear decision summary should explain the outcome, the information considered, and any next steps. I recommend asking for clarification when wording is unclear, while keeping all account-specific information out of public discussions.',
            ],
            [
                'author' => 'kronos.p@example.com',
                'slug' => 'draft-notes-for-closing-the-process',
                'title' => 'Draft notes for closing the process',
                'claim_stage' => ClaimStage::Closed,
                'status' => PostStatus::Draft,
                'published_at' => null,
                'content' => 'These private draft notes collect the general lessons I may share after checking that every identifying detail has been removed from the final version.',
            ],
        ];

        foreach ($posts as $attributes) {
            $author = User::query()->where('email', $attributes['author'])->firstOrFail();
            unset($attributes['author']);

            $post = Post::query()->firstOrNew(['slug' => $attributes['slug']]);
            $post->forceFill([...$attributes, 'user_id' => $author->getKey()])->save();
        }
    }
}
