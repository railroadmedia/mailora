<?php namespace Railroad\Mailora\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class MailoraServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->publishes(
            [
                __DIR__ . '/../../config/mailora-config.php' => config_path('mailora-config.php')
            ]
        );
        $this->loadRoutesFrom(__DIR__.'/../../routes/mailora.php');
        $this->loadViewsFrom(__DIR__ . '../../resources/views', 'mailora');
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