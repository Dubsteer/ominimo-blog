<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_management_enforces_administrator_boundary(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));

        foreach ([
            User::factory()->create(),
            User::factory()->moderator()->create(),
        ] as $user) {
            $this->actingAs($user)
                ->get(route('admin.users.index'))
                ->assertForbidden();
        }

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSeeText('Role management');
    }

    public function test_administrator_can_filter_users(): void
    {
        $administrator = User::factory()->administrator()->create();
        $match = User::factory()->moderator()->create([
            'name' => 'Review Team Member',
            'email' => 'reviewer@example.com',
        ]);
        $other = User::factory()->create([
            'name' => 'Community Member',
            'email' => 'community@example.com',
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.users.index', [
                'q' => ' Review Team ',
                'role' => ' moderator ',
            ]))
            ->assertOk()
            ->assertViewHas('filters', [
                'q' => 'Review Team',
                'role' => UserRole::Moderator->value,
            ])
            ->assertSeeText($match->email)
            ->assertDontSeeText($other->email);
    }

    public function test_administrator_can_update_another_users_role(): void
    {
        $administrator = User::factory()->administrator()->create();
        $user = User::factory()->create();

        $this->actingAs($administrator)
            ->patch(route('admin.users.role.update', $user), [
                'role' => UserRole::Moderator->value,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame(UserRole::Moderator, $user->fresh()->role);
    }

    public function test_non_administrators_cannot_update_roles(): void
    {
        $target = User::factory()->create();

        foreach ([
            User::factory()->create(),
            User::factory()->moderator()->create(),
        ] as $actor) {
            $this->actingAs($actor)
                ->patch(route('admin.users.role.update', $target), [
                    'role' => UserRole::Administrator->value,
                ])
                ->assertForbidden();
        }

        $this->assertSame(UserRole::User, $target->fresh()->role);
    }

    public function test_administrator_cannot_change_their_own_role(): void
    {
        $administrator = User::factory()->administrator()->create();

        $this->actingAs($administrator)
            ->patch(route('admin.users.role.update', $administrator), [
                'role' => UserRole::User->value,
            ])
            ->assertForbidden();

        $this->assertSame(UserRole::Administrator, $administrator->fresh()->role);
    }

    public function test_invalid_roles_are_rejected(): void
    {
        $administrator = User::factory()->administrator()->create();
        $user = User::factory()->create();

        $this->actingAs($administrator)
            ->patch(route('admin.users.role.update', $user), [
                'role' => 'super-administrator',
            ])
            ->assertSessionHasErrors('role');

        $this->assertSame(UserRole::User, $user->fresh()->role);
    }

    public function test_invalid_user_search_filters_are_rejected(): void
    {
        $administrator = User::factory()->administrator()->create();

        $this->actingAs($administrator)
            ->get(route('admin.users.index', ['role' => 'owner']))
            ->assertSessionHasErrors('role');
    }

    public function test_one_administrator_can_demote_another_when_access_remains(): void
    {
        $administrator = User::factory()->administrator()->create();
        $otherAdministrator = User::factory()->administrator()->create();

        $this->actingAs($administrator)
            ->patch(route('admin.users.role.update', $otherAdministrator), [
                'role' => UserRole::Moderator->value,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame(UserRole::Moderator, $otherAdministrator->fresh()->role);
        $this->assertSame(UserRole::Administrator, $administrator->fresh()->role);
    }
}
