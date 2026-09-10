<?php

namespace App\Http\Requests\Post;

use App\Enums\ClaimStage;
use App\Enums\PostStatus;
use App\Rules\SafePublicContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class PostRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['bail', 'required', 'string', 'max:160', new SafePublicContent],
            'content' => ['bail', 'required', 'string', 'min:30', 'max:50000', new SafePublicContent],
            'claim_stage' => ['required', Rule::enum(ClaimStage::class)],
            'status' => ['required', Rule::enum(PostStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim((string) $this->input('title')),
            'content' => trim((string) $this->input('content')),
        ]);
    }
}
