<?php

namespace App\Models;

use App\Enums\CommentStatus;
use App\Policies\CommentPolicy;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['comment', 'status'])]
#[UsePolicy(CommentPolicy::class)]
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'active',
    ];

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Include hidden comments only for their author, the post owner, and moderators.
     *
     * @param  Builder<Comment>  $query
     * @return Builder<Comment>
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if ($user?->canModerateContent()) {
            return $query;
        }

        return $query->where(function (Builder $visibility) use ($user): void {
            $visibility->where('status', CommentStatus::Active->value);

            if ($user !== null) {
                $visibility
                    ->orWhere('user_id', $user->getKey())
                    ->orWhereHas('post', fn (Builder $posts) => $posts->where('user_id', $user->getKey()));
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
        ];
    }
}
