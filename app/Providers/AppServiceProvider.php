<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
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
	if (str_starts_with(config('app.url'), 'https://')) {
	        URL::forceScheme('https');
	}
        Model::preventSilentlyDiscardingAttributes($this->app->isLocal());

        $this->loadMigrationsFrom(database_path('migrations/0.0.1'));
        $this->loadMigrationsFrom(database_path('migrations/0.0.2'));
        $this->loadMigrationsFrom(database_path('migrations/0.0.3'));
        $this->loadMigrationsFrom(database_path('migrations/0.0.4'));
        $this->loadMigrationsFrom(database_path('migrations/0.0.5'));
    }
}
