<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        RateLimiter::for('comments', function (Request $request): Limit {
            if ($request->user() !== null) {
                return Limit::perMinute(15)->by('user:'.$request->user()->getAuthIdentifier());
            }

            // Hash the address before using it as a transient throttle key.
            return Limit::perMinute(5)->by('guest:'.hash('sha256', (string) $request->ip()));
        });
    }
}
