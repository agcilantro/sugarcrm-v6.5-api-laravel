<?php

namespace Daryl\SugarAPI;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;

class SugarAPIServiceProvider extends ServiceProvider {

	/**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/config.php' => config_path('l5-sugarcrm.php'),
        ], 'config');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/config.php', 'l5-sugarcrm'
        );

        App::bind('sugarcrm', function() {
            return new SugarAPI;
        });

    }
}