<?php

namespace App\Http\Requests\Admin;

use App\Enums\ClaimStage;
use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Http\Requests\SearchRequest;
use Illuminate\Validation\Rule;

class SearchModerationRequest extends SearchRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canModerateContent() ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'post_q' => ['nullable', 'string', 'max:100'],
            'post_status' => ['nullable', Rule::enum(PostStatus::class)],
            'post_claim_stage' => ['nullable', Rule::enum(ClaimStage::class)],
            'post_author' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'comment_q' => ['nullable', 'string', 'max:100'],
            'comment_status' => ['nullable', Rule::enum(CommentStatus::class)],
            'comment_author' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'comment_origin' => ['nullable', Rule::in(['member', 'guest'])],
        ];
    }
}
