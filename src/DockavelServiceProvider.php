<?php

namespace Savannabits\Dockavel;

use Illuminate\Support\ServiceProvider;

class DockavelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'dockavel');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'dockavel');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../docker' => base_path('docker'),
            ], 'dockavel-config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/dockavel'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/dockavel'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/dockavel'),
            ], 'lang');*/

            // Registering package commands.
             $this->commands([
                 Dockavel::class
             ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
//        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'dockavel');

        /*// Register the main class to use with the facade
        $this->app->singleton('dockavel', function () {
            return new Dockavel;
        });*/
    }
}
