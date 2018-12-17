<?php namespace Railroad\Mailora\Providers;

use Illuminate\Support\ServiceProvider;

class MailoraServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes(
            [
                __DIR__ . '/../../config/mailora.php' => config_path('mailora.php')
            ]
        );
        $this->loadRoutesFrom(__DIR__.'/../../routes/mailora.php');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'mailora');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }
}