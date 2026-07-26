<?php

namespace Atik\Pdf;

use Illuminate\Support\ServiceProvider;

class AtikPdfServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-pdf-excel.php', 'laravel-pdf-excel'
        );

        $this->app->singleton(AtikPdfManager::class, function ($app) {
            return new AtikPdfManager($app);
        });

        $this->app->bind('laravel-pdf-excel', function ($app) {
            return $app->make(AtikPdfManager::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/laravel-pdf-excel.php' => config_path('laravel-pdf-excel.php'),
            ], 'laravel-pdf-excel-config');
            
            // Allow publishing python service if they want it inside the laravel app
            $this->publishes([
                __DIR__.'/../python-service' => base_path('python-service'),
            ], 'laravel-pdf-excel-python-service');
        }
    }
}
