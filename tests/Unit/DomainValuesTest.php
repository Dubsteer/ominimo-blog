<?php

namespace Tests\Unit;

use App\Enums\ClaimStage;
use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Enums\UserRole;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class DomainValuesTest extends TestCase
{
    public function test_claim_stages_have_stable_public_values_and_labels(): void
    {
        $this->assertSame([
            'reporting' => 'Reporting',
            'assessment' => 'Assessment',
            'review' => 'Review',
            'decision' => 'Decision',
            'payment' => 'Payment',
            'closed' => 'Closed',
        ], $this->labels(ClaimStage::cases()));
    }

    public function test_statuses_have_stable_public_values_and_labels(): void
    {
        $this->assertSame([
            'draft' => 'Draft',
            'published' => 'Published',
        ], $this->labels(PostStatus::cases()));

        $this->assertSame([
            'active' => 'Active',
            'hidden' => 'Hidden',
        ], $this->labels(CommentStatus::cases()));
    }

    public function test_role_capabilities_and_role_matching(): void
    {
        $this->assertSame([
            'user' => 'User',
            'moderator' => 'Moderator',
            'administrator' => 'Administrator',
        ], $this->labels(UserRole::cases()));

        foreach (UserRole::cases() as $role) {
            $user = (new User)->forceFill(['role' => $role]);

            $this->assertSame($role !== UserRole::User, $role->canModerateContent());
            $this->assertSame($role !== UserRole::User, $user->canModerateContent());
            $this->assertSame($role === UserRole::Administrator, $user->isAdministrator());
            $this->assertTrue($user->hasAnyRole([$role->value]));
            $this->assertTrue($user->hasAnyRole([$role]));
            $this->assertFalse($user->hasAnyRole(['unknown-role']));
        }
    }

    /**
     * @param  array<int, ClaimStage|CommentStatus|PostStatus|UserRole>  $cases
     * @return array<string, string>
     */
    private function labels(array $cases): array
    {
        $labels = [];

        foreach ($cases as $case) {
            $labels[$case->value] = $case->label();
        }

        return $labels;
    }
}
