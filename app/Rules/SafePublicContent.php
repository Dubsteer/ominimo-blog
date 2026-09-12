<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafePublicContent implements ValidationRule
{
    /**
     * These patterns catch high-confidence identifiers without attempting to
     * infer sensitive medical or financial meaning from ordinary prose.
     *
     * @var array<int, string>
     */
    private const BLOCKED_PATTERNS = [
        '/\b(?:claim|policy|reference)\s*(?:number|no\.?|id|#)\s*[:#-]?\s*(?:is\s+)?[a-z0-9][a-z0-9-]{4,}\b/i',
        '/\b(?:clm|pol|ref)[-_ ]?\d{4,}\b/i',
        '/\b[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}\b/i',
        '/(?:\d[ -]*){13,19}/',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        foreach (self::BLOCKED_PATTERNS as $pattern) {
            if (preg_match($pattern, $value) === 1) {
                $fail('Remove claim or policy numbers, contact details, and payment data before saving.');

                return;
            }
        }
    }
}
