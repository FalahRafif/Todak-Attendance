<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
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
        Model::preventSilentlyDiscardingAttributes($this->app->isLocal());

        $this->loadMigrationsFrom(database_path('migrations/0.0.1'));
        $this->loadMigrationsFrom(database_path('migrations/0.0.2'));
        $this->loadMigrationsFrom(database_path('migrations/0.0.3'));
        $this->loadMigrationsFrom(database_path('migrations/0.0.4'));
    }
}
