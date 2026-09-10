<?php

namespace App\Models;

use App\Enums\ClaimStage;
use App\Enums\PostStatus;
use App\Policies\PostPolicy;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['title', 'content', 'claim_stage', 'status'])]
#[UsePolicy(PostPolicy::class)]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'draft',
    ];

    protected static function booted(): void
    {
        static::creating(function (Post $post): void {
            if (blank($post->slug)) {
                $base = Str::slug(Str::limit($post->title, 120, '')) ?: 'post';
                $post->slug = $base.'-'.Str::lower(Str::random(8));
            }
        });

        static::saving(function (Post $post): void {
            // Publication time is derived on the server so clients cannot forge it.
            $post->published_at = $post->status === PostStatus::Published
                ? ($post->published_at ?? now())
                : null;
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published->value);
    }

    /**
     * Search through post text in the database, using MySQL's full-text index
     * in local and production environments and a portable fallback in tests.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        if ($query->getConnection()->getDriverName() === 'mysql' && mb_strlen($term) >= 3) {
            return $query->whereFullText(['title', 'content'], $term);
        }

        return $query->where(function (Builder $search) use ($term): void {
            $search
                ->where('title', 'like', '%'.$term.'%')
                ->orWhere('content', 'like', '%'.$term.'%');
        });
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if ($user?->canModerateContent()) {
            return $query;
        }

        return $query->where(function (Builder $visibility) use ($user): void {
            $visibility->where('status', PostStatus::Published->value);

            if ($user !== null) {
                $visibility->orWhere('user_id', $user->getKey());
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'claim_stage' => ClaimStage::class,
            'status' => PostStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
