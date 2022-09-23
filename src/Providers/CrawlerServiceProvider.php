<?php

namespace Juzaweb\Crawler\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Juzaweb\Crawler\Commands\CrawContentCommand;
use Juzaweb\Crawler\Commands\CrawLinkCommand;
use Juzaweb\Crawler\Commands\LinkCrawlerCommand;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\CrawlerAction;
use Juzaweb\CMS\Facades\ActionRegister;
use Juzaweb\CMS\Support\ServiceProvider;
use Juzaweb\Crawler\Support\Crawler;

class CrawlerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        ActionRegister::register(
            [
                //CrawlerAction::class
            ]
        );

        $this->commands(
            [
                LinkCrawlerCommand::class,
                //CrawLinkCommand::class,
                //CrawContentCommand::class
            ]
        );

        $this->app->booted(
            function () {
                $schedule = $this->app->make(Schedule::class);
                //$schedule->command('crawler:content')->everyMinute();
                //$schedule->command('crawler:link')->everyFiveMinutes();
            }
        );
    }

    public function register()
    {
        $this->app->singleton(CrawlerContract::class, Crawler::class);
    }
}
