<?php

namespace Tests\Unit;

use App\Rules\SafePublicContent;
use PHPUnit\Framework\TestCase;

class SafePublicContentTest extends TestCase
{
    public function test_it_rejects_high_confidence_identifiers(): void
    {
        foreach ([
            'My claim number is AB-12345.',
            'Policy ID: ZX987654.',
            'Please look up CLM-123456.',
            'Email me at customer@example.com.',
            'The card number is 4111 1111 1111 1111.',
        ] as $content) {
            $this->assertSame(
                ['Remove claim or policy numbers, contact details, and payment data before saving.'],
                $this->failuresFor($content),
                $content,
            );
        }
    }

    public function test_it_allows_general_discussion_and_non_string_values(): void
    {
        foreach ([
            'The assessment stage took longer than expected, but the next steps were clear.',
            'I asked for a written explanation of the decision.',
            null,
            12345,
        ] as $content) {
            $this->assertSame([], $this->failuresFor($content));
        }
    }

    /**
     * @return array<int, string>
     */
    private function failuresFor(mixed $content): array
    {
        $failures = [];

        (new SafePublicContent)->validate(
            'content',
            $content,
            function (string $message) use (&$failures): void {
                $failures[] = $message;
            },
        );

        return $failures;
    }
}
