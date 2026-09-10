<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_routes_require_authentication(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->patch(route('profile.update'))->assertRedirect(route('login'));
        $this->put(route('password.update'))->assertRedirect(route('login'));
        $this->delete(route('profile.destroy'))->assertRedirect(route('login'));
    }

    public function test_authenticated_account_pages_render(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Hello, '.$user->name);

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Your current role is User.');
    }

    public function test_a_user_can_update_profile_information(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ])->assertSessionHas('status', 'profile-updated');

        $user->refresh();

        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_a_user_can_update_their_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertSessionHas('status', 'password-updated');

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }

    public function test_the_current_password_is_required_to_change_the_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertSessionHasErrorsIn('updatePassword', 'current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_a_user_can_delete_their_account_with_the_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->delete(route('profile.destroy'), [
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertModelMissing($user);
    }

    public function test_an_incorrect_password_does_not_delete_the_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ])->assertSessionHasErrorsIn('deleteProfile', 'password');

        $this->assertAuthenticatedAs($user);
        $this->assertModelExists($user);
    }
}
