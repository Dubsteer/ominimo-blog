<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorizonAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrators_can_view_horizon_outside_local_development(): void
    {
        $this->app->instance('env', 'production');

        $this->get('/horizon')->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->get('/horizon')
            ->assertForbidden();

        $this->actingAs(User::factory()->administrator()->create())
            ->get('/horizon')
            ->assertOk();
    }
}
