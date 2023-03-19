<?php

namespace Juzaweb\Crawler\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\CMS\Traits\ResourceController;
use Juzaweb\Crawler\Http\Datatables\CrawlerPageDatatable;
use Juzaweb\Crawler\Models\CrawlerPage;

class PageController extends BackendController
{
    use ResourceController;

    protected string $viewPrefix = 'crawler::page';

    protected function getDataTable(...$params): CrawlerPageDatatable
    {
        return new CrawlerPageDatatable();
    }

    protected function validator(array $attributes, ...$params): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make(
            $attributes,
            [
                // Rules
            ]
        );
    }

    protected function getModel(...$params): string
    {
        return CrawlerPage::class;
    }

    protected function getTitle(...$params): string
    {
        return trans('crawler::content.crawler_pages');
    }
}
