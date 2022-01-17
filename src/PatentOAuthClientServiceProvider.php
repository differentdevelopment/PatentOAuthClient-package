<?php

namespace Different\PatentOAuthClient;

use Different\PatentOAuthClient\app\Console\Commands\PasUserBatchSync;
use Illuminate\Support\ServiceProvider;

class PatentOAuthClientServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if (empty(config("backpack")) || config("backpack.base.setup_auth_routes") === false) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/routes.php');
        }

        $this->commands([
            PasUserBatchSync::class,
        ]);

        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'patent oauth client');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'patent-oauth-client');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('patent-oauth-client.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/patent oauth client'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/patent oauth client'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/patent oauth client'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'patent-oauth-client');

        // Register the main class to use with the facade
        $this->app->singleton('patent-oauth-client', function () {
            return new PatentOAuthClient;
        });
    }
}
