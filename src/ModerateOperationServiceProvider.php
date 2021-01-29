<?php

namespace CompanyName\ModerateOperation;

use Illuminate\Support\ServiceProvider;
use phpDocumentor\Reflection\Types\Void_;

class ModerateOperationServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
         $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'companyname');
         $this->loadViewsFrom(__DIR__.'/../resources/views', 'companyname');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
//    public function register(): void
//    {
////        $this->mergeConfigFrom(__DIR__.'/../config/moderateoperation.php', 'moderateoperation');
//
//        // Register the service the package provides.
//        $this->app->singleton('moderateoperation', function ($app) {
//            return new ModerateOperation;
//        });
//    }
//
//    /**
//     * Get the services provided by the provider.
//     *
//     * @return array
//     */
//    public function provides()
//    {
//        return ['moderateoperation'];
//    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {

        // Publishing the views.
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/backpack'),
        ], 'moderateoperation.views');
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/companyname'),
        ], 'moderateoperation.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/companyname'),
        ], 'moderateoperation.views');*/

        // Publishing the translation files.
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/companyname'),
        ], 'moderateoperation.views');

        // Registering package commands.
        // $this->commands([]);
    }
}
