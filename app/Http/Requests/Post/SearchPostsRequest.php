<?php

namespace App\Http\Requests\Post;

use App\Enums\ClaimStage;
use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SearchPostsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::forUser($this->user())->allows('viewAny', Post::class);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'claim_stage' => ['nullable', Rule::enum(ClaimStage::class)],
            'status' => ['nullable', Rule::enum(PostStatus::class)],
            'author' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'discussion' => ['nullable', Rule::in(['with_comments', 'without_comments'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['q', 'claim_stage', 'status', 'author', 'discussion'] as $key) {
            if ($this->has($key) && is_string($this->input($key))) {
                $value = trim($this->input($key));
                $normalized[$key] = $value === '' ? null : $value;
            }
        }

        $this->merge($normalized);
    }
}
