<?php

namespace Juzaweb\Crawler\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Juzaweb\CMS\Contracts\PostImporterContract;
use Juzaweb\CMS\Facades\ActionRegister;
use Juzaweb\CMS\Support\HookAction;
use Juzaweb\CMS\Support\ServiceProvider;
use Juzaweb\Crawler\Actions\ConfigAction;
use Juzaweb\Crawler\Actions\CrawlerAction;
use Juzaweb\Crawler\Commands\AutoContentCrawlerCommand;
use Juzaweb\Crawler\Commands\AutoLinkCrawlerCommand;
use Juzaweb\Crawler\Commands\AutoPostCommand;
use Juzaweb\Crawler\Commands\AutoTranslateCommand;
use Juzaweb\Crawler\Commands\TestContentCrawlerCommand;
use Juzaweb\Crawler\Commands\TestLinkCrawlerCommand;
use Juzaweb\Crawler\Commands\TestTranslateCrawlerCommand;
use Juzaweb\Crawler\Contracts\CrawlerContract;
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
                TestLinkCrawlerCommand::class,
                TestContentCrawlerCommand::class,
                AutoLinkCrawlerCommand::class,
                AutoContentCrawlerCommand::class,
                AutoTranslateCommand::class,
                AutoPostCommand::class,
                TestTranslateCrawlerCommand::class,
            ]
        );

        ActionRegister::register(
            [
                CrawlerAction::class,
                ConfigAction::class,
            ]
        );

        $this->app->booted(
            function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command('crawler:contents')->everyMinute();
                $schedule->command('crawler:links')->everyFiveMinutes();
                $schedule->command('crawler:translate')->everyFiveMinutes();
            }
        );
    }

    public function register()
    {
        $this->app->singleton(
            CrawlerContract::class,
            function ($app) {
                return new Crawler($app[PostImporterContract::class]);
            }
        );
    }
}
