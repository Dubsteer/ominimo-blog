<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Restrict the operational dashboard to administrators outside local development.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', fn (?User $user): bool => $user?->isAdministrator() ?? false);
    }
}
