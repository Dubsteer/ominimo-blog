<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Nikola Petrović',
                'email' => 'nikola.petrovic@example.com',
                'password' => '!Nikola.p1',
                'role' => UserRole::User,
            ],
            [
                'name' => 'Aleksa Jovanović',
                'email' => 'aleksa.jovanovic@example.com',
                'password' => '!Aleksa.j2',
                'role' => UserRole::Moderator,
            ],
            [
                'name' => 'Bogdan Marković',
                'email' => 'bogdan.markovic@example.com',
                'password' => '!Bogdan.m3',
                'role' => UserRole::Administrator,
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->firstOrNew(['email' => $account['email']]);

            $user->forceFill([
                ...$account,
                'email_verified_at' => now(),
            ])->save();
        }
    }
}
