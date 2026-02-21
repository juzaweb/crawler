<?php

use Juzaweb\Modules\Crawler\Http\Controllers\CrawlerSourceController;
use Juzaweb\Modules\Crawler\Http\Controllers\CrawlerPageController;
use Juzaweb\Modules\Crawler\Http\Controllers\CrawlerLogController;

Route::admin('crawler-logs', CrawlerLogController::class);
Route::admin('crawler-sources', CrawlerSourceController::class);
Route::get('crawler-sources/get-components', [CrawlerSourceController::class, 'getComponents'])->name('admin.crawler.get-components');
Route::admin('crawler-sources/{sourceId}/pages', CrawlerPageController::class);
