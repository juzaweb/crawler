<?php

namespace Juzaweb\Modules\Crawler\Providers;

use Juzaweb\Modules\Admin\Providers\ServiceProvider;
use Illuminate\Support\Facades\File;

class CrawlerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        //

        $this->registerMenus();
    }

    public function register(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__ . '/../../Database/migrations');
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerMenus(): void
    {
        if (File::missing(storage_path('app/installed'))) {
            return;
        }

        //
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('crawler.php'),
        ], 'crawler-config');
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'crawler');
    }

    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'crawler');
        $this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang');
    }

    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/crawler');

        $sourcePath = __DIR__ . '/../resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', 'crawler-module-views']);

        $this->loadViewsFrom($sourcePath, 'crawler');
    }
}
