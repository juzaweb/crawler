<?php

namespace Juzaweb\Crawler\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Juzaweb\Backend\Models\Post;
use Juzaweb\CMS\Facades\ActionRegister;
use Juzaweb\CMS\Facades\MacroableModel;
use Juzaweb\CMS\Support\HookAction;
use Juzaweb\CMS\Support\ServiceProvider;
use Juzaweb\Crawler\Actions\ConfigAction;
use Juzaweb\Crawler\Actions\CrawlerAction;
use Juzaweb\Crawler\Commands\AutoTranslateCommand;
use Juzaweb\Crawler\Commands\Crawler\AutoContentCrawlerCommand;
use Juzaweb\Crawler\Commands\Crawler\AutoLinkCrawlerCommand;
use Juzaweb\Crawler\Commands\CrawlerLinkManualCommand;
use Juzaweb\Crawler\Commands\FindLinkCommand;
use Juzaweb\Crawler\Commands\Poster\AutoPostCommand;
use Juzaweb\Crawler\Commands\Poster\AutoPublishPostCommand;
use Juzaweb\Crawler\Commands\ReplaceContentTranslateAgainCommand;
use Juzaweb\Crawler\Commands\ReplaceTranslateAgainCommand;
use Juzaweb\Crawler\Commands\Tester\TestContentCrawlerCommand;
use Juzaweb\Crawler\Commands\Tester\TestLinkCrawlerCommand;
use Juzaweb\Crawler\Commands\Tester\TestTranslateCrawlerCommand;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerContent;
use Juzaweb\Crawler\Support\Crawler;

class CrawlerServiceProvider extends ServiceProvider
{
    public function boot(): void
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
                ReplaceTranslateAgainCommand::class,
                ReplaceContentTranslateAgainCommand::class,
                FindLinkCommand::class,
                AutoPublishPostCommand::class,
                CrawlerLinkManualCommand::class,
            ]
        );

        ActionRegister::register(
            [
                CrawlerAction::class,
                ConfigAction::class,
            ]
        );

        MacroableModel::addMacro(
            Post::class,
            'crawlerContent',
            fn () => $this->hasOne(
                CrawlerContent::class,
                'post_id',
                'id'
            )
        );

        $this->app->booted(
            function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command('crawler:contents')->everyFiveMinutes();
                $schedule->command('crawler:links')->everyFiveMinutes();
                $schedule->command('crawler:translate')->everyFiveMinutes();
                $schedule->command(AutoPostCommand::class)->hourlyAt('9');
                $schedule->command(AutoPublishPostCommand::class)->hourlyAt('12');
            }
        );
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/crawler.php', 'crawler');

        $this->app->singleton(
            CrawlerContract::class,
            function ($app) {
                return new Crawler($app);
            }
        );
    }
}
