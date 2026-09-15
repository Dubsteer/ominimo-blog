<?php

namespace Database\Seeders;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $comments = [
            [
                'post' => 'preparing-for-the-first-assessment',
                'author' => 'aleksa.jovanovic@example.com',
                'status' => CommentStatus::Active,
                'comment' => 'A short, date-ordered timeline also made my assessment questions much easier to explain.',
            ],
            [
                'post' => 'preparing-for-the-first-assessment',
                'author' => null,
                'status' => CommentStatus::Active,
                'comment' => 'This is useful guidance for preparing without sharing private details. Thank you.',
            ],
            [
                'post' => 'questions-to-ask-during-review',
                'author' => 'nikola.petrovic@example.com',
                'status' => CommentStatus::Active,
                'comment' => 'Writing down the expected next step helped me follow the review process too.',
            ],
            [
                'post' => 'understanding-a-claim-decision',
                'author' => null,
                'status' => CommentStatus::Hidden,
                'comment' => 'This seeded comment demonstrates the hidden moderation state.',
            ],
        ];

        foreach ($comments as $attributes) {
            $post = Post::query()->where('slug', $attributes['post'])->firstOrFail();
            $author = $attributes['author'] === null
                ? null
                : User::query()->where('email', $attributes['author'])->firstOrFail();

            $comment = Comment::query()->firstOrNew([
                'post_id' => $post->getKey(),
                'comment' => $attributes['comment'],
            ]);

            $comment->forceFill([
                'user_id' => $author?->getKey(),
                'status' => $attributes['status'],
            ])->save();
        }
    }
}
