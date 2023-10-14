<?php

use Juzaweb\Crawler\Http\Controllers\PageController;
use Juzaweb\Crawler\Http\Controllers\StatsController;
use Juzaweb\Crawler\Http\Controllers\TestingController;
use Juzaweb\Crawler\Http\Controllers\WebsiteController;
use Juzaweb\Crawler\Http\Controllers\ContentController;
use Juzaweb\Crawler\Http\Controllers\ImportLinkController;
use Juzaweb\Crawler\Http\Controllers\ImportPageController;

Route::jwResource('crawler/websites', WebsiteController::class);

Route::jwResource(
    'crawler/websites/{website_id}/contents',
    ContentController::class,
    ['name' => 'crawler.websites.contents']
);

Route::post(
    'crawler/websites/{website_id}/contents/re-get/{id}',
    [ContentController::class, 'reGet']
)->name('crawler.websites.contents.re-get');

Route::post(
    'crawler/websites/{website_id}/contents/re-translate/{id}',
    [ContentController::class, 'reTranslate']
)->name('crawler.websites.contents.re-translate');

Route::jwResource('crawler/websites/{website_id}/pages', PageController::class, ['name' => 'crawler.websites.pages']);

Route::get('crawler/stats', [StatsController::class, 'index']);
Route::get('crawler/stats/crawler-chars', [StatsController::class, 'crawlerChart'])
    ->name('crawler.stats.crawler-chars');

Route::get('crawler/testing', [TestingController::class, 'index']);
Route::post('crawler/testing', [TestingController::class, 'test']);

Route::get('crawler/import-links', [ImportLinkController::class, 'index']);
Route::post('crawler/import-links', [ImportLinkController::class, 'import']);

Route::get('crawler/import-pages', [ImportPageController::class, 'index']);
Route::get('crawler/import-pages/website-info', [ImportPageController::class, 'getWebsiteInfo']);
Route::post('crawler/import-pages', [ImportPageController::class, 'import']);
Route::post('crawler/import-pages/find', [ImportPageController::class, 'find'])
    ->name('crawler.import-pages.find');
