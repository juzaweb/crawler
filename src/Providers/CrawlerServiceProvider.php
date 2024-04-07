<?php

namespace Juzaweb\Crawler\Providers;

use Illuminate\Support\Collection;
use Juzaweb\Backend\Models\Post;
use Juzaweb\CMS\Facades\ActionRegister;
use Juzaweb\CMS\Facades\MacroableModel;
use Juzaweb\CMS\Support\HookAction;
use Juzaweb\CMS\Support\ServiceProvider;
use Juzaweb\Crawler\Actions\ImportAction;
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

        ActionRegister::register(
            [
                ImportAction::class,
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
    }

    public function register(): void
    {
        $this->app->singleton(
            CrawlerContract::class,
            function ($app) {
                return new Crawler($app);
            }
        );
    }
}
