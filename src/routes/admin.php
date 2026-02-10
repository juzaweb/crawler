<?php

use Juzaweb\Modules\Crawler\Http\Controllers\CrawlerSourceController;
use Juzaweb\Modules\Crawler\Http\Controllers\CrawlerPageController;

Route::admin('crawler-sources', CrawlerSourceController::class);
Route::admin('crawler-sources/{sourceId}/pages', CrawlerPageController::class);
