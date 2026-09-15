<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_database_seeder_creates_idempotent_representative_accounts(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(3, User::query()->count());
        $this->assertSame(4, Post::query()->count());
        $this->assertSame(4, Comment::query()->count());
        $this->assertDatabaseHas('users', ['email' => 'nikola.petrovic@example.com', 'role' => UserRole::User->value]);
        $this->assertDatabaseHas('users', ['email' => 'aleksa.jovanovic@example.com', 'role' => UserRole::Moderator->value]);
        $this->assertDatabaseHas('users', ['email' => 'bogdan.markovic@example.com', 'role' => UserRole::Administrator->value]);
    }
}
