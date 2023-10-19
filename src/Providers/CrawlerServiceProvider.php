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
use Juzaweb\Crawler\Commands;
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
                    return $this->globalData->get("crawler_template.{$key}");
                }

                return new Collection($this->globalData->get('crawler_template'));
            }
        );

        $this->commands(
            [
                Commands\Tester\TestLinkCrawlerCommand::class,
                Commands\Tester\TestContentCrawlerCommand::class,
                Commands\Crawler\AutoLinkCrawlerCommand::class,
                Commands\Crawler\AutoContentCrawlerCommand::class,
                Commands\Translate\AutoTranslateCommand::class,
                Commands\Poster\AutoPostCommand::class,
                Commands\Tester\TestTranslateCrawlerCommand::class,
                Commands\ReplaceTranslateAgainCommand::class,
                Commands\ReplaceContentTranslateAgainCommand::class,
                Commands\FindLinkCommand::class,
                Commands\Poster\AutoPublishPostCommand::class,
                Commands\CrawlerLinkManualCommand::class,
                Commands\ImportTemplateCommand::class,
                Commands\Crawler\AutoContentCrawlerWithBusCommand::class,
                Commands\Translate\TranslateCommand::class,
                Commands\MakeTemplateCommand::class,
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
                $schedule->command(Commands\Crawler\AutoLinkCrawlerCommand::class)->everyFiveMinutes();
                $schedule->command(Commands\Translate\AutoTranslateCommand::class)->everyFiveMinutes();
                $schedule->command(Commands\Crawler\AutoContentCrawlerWithBusCommand::class)->everyFiveMinutes();
                $schedule->command(Commands\Poster\AutoPublishPostCommand::class)->hourlyAt('12');
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
