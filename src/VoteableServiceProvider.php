<?php

namespace Yoeunes\Voteable;

use Illuminate\Support\ServiceProvider;

class VoteableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/voteable.php' => config_path('voteable.php'),
        ], 'config');

        if (! class_exists('CreateVotesTable')) {
            $this->publishes([
                __DIR__.'/../migrations/create_votes_table.php' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_votes_table.php'),
            ], 'migrations');
        }
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/voteable.php', 'voteable');
    }
}
