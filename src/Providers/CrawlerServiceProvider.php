<?php

namespace Juzaweb\Crawler\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Juzaweb\CMS\Support\HookAction;
use Juzaweb\Crawler\Commands\AutoContentCrawlerCommand;
use Juzaweb\Crawler\Commands\AutoLinkCrawlerCommand;
use Juzaweb\Crawler\Commands\ContentCrawlerCommand;
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
        HookAction::macro(
            'registerCrawlerTemplate',
            function (string $key, array $args = []) {
                $defaults = [
                    'name' => '',
                    'class' => '',
                    'key' => $key,
                ];

                $args = array_merge($defaults, $args);

                $this->globalData->set(
                    "crawler_template.{$key}",
                    new Collection($args)
                );
            }
        );

        HookAction::macro(
            'getCrawlerTemplates',
            function (string $key = null) {
                if ($key) {
                    return $this->globalData->get('crawler_template.' . $key);
                }

                return new Collection($this->globalData->get('crawler_template'));
            }
        );

        $this->commands(
            [
                LinkCrawlerCommand::class,
                ContentCrawlerCommand::class,
                AutoLinkCrawlerCommand::class,
                AutoContentCrawlerCommand::class,
            ]
        );

        ActionRegister::register(
            [
                CrawlerAction::class
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
