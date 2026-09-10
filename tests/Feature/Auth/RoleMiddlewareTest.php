<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'role:moderator,administrator'])
            ->get('/_testing/moderation', fn () => response()->noContent());
    }

    public function test_a_guest_is_redirected_to_login(): void
    {
        $this->get('/_testing/moderation')->assertRedirect(route('login'));
    }

    public function test_a_regular_user_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/_testing/moderation')
            ->assertForbidden();
    }

    public function test_a_moderator_can_pass_the_role_boundary(): void
    {
        $this->actingAs(User::factory()->moderator()->create())
            ->get('/_testing/moderation')
            ->assertNoContent();
    }

    public function test_an_administrator_can_pass_the_role_boundary(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get('/_testing/moderation')
            ->assertNoContent();
    }
}
