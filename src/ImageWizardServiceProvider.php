<?php

namespace DaiyanMozumder\ImageWizard;

use Illuminate\Support\ServiceProvider;

class ImageWizardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/image-wizard.php', 'image-wizard');

        $this->app->singleton('image-wizard', function ($app) {
            return new ImageWizardManager($app['config']->get('image-wizard'));
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/image-wizard.php' => config_path('image-wizard.php'),
            ], 'image-wizard-config');
        }
    }
}
