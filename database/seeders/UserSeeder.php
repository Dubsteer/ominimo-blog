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
                'name' => 'Kronos P.',
                'email' => 'kronos.p@example.com',
                'password' => '!Kronos.p1',
                'role' => UserRole::User,
            ],
            [
                'name' => 'Themis R.',
                'email' => 'themis.r@example.com',
                'password' => '!Themis.t2',
                'role' => UserRole::Moderator,
            ],
            [
                'name' => 'Atlas M.',
                'email' => 'atlas.m@example.com',
                'password' => '!Atlas.a3',
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
