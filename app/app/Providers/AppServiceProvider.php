<?php

namespace App\Providers;

use App\Contracts\Nyt\NytApiServiceContract;
use App\Proxy\Nyt\NytApiServiceProxy;
use App\Services\Nyt\NytApiService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NytApiServiceContract::class, function () {
            return new NytApiServiceProxy(new NytApiService());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

    }
}
