<?php

use Juzaweb\Crawler\Http\Controllers\PageController;
use Juzaweb\Crawler\Http\Controllers\WebsiteController;

Route::jwResource('crawler/websites', WebsiteController::class);
Route::jwResource('crawler/websites/{id}/pages', PageController::class);
