<?php

namespace Juzaweb\Crawler\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Juzaweb\Crawler\Commands\CrawContentCommand;
use Juzaweb\Crawler\Commands\CrawLinkCommand;
use Juzaweb\Crawler\CrawlerAction;
use Juzaweb\CMS\Facades\ActionRegister;
use Juzaweb\CMS\Support\ServiceProvider;

class CrawlerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        ActionRegister::register(
            [
                CrawlerAction::class
            ]
        );

        $this->commands(
            [
                CrawLinkCommand::class,
                CrawContentCommand::class
            ]
        );

        $this->app->booted(
            function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command('crawler:content')->everyMinute();
                $schedule->command('crawler:link')->everyFiveMinutes();
            }
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //$this->app->register(RouteServiceProvider::class);
    }
}
