<?php

namespace Juzaweb\Crawler\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Juzaweb\Backend\Models\Taxonomy;
use Juzaweb\CMS\Facades\HookAction;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\CMS\Traits\ResourceController;
use Juzaweb\Crawler\Http\Datatables\CrawlerPageDatatable;
use Juzaweb\Crawler\Models\CrawlerPage;

class PageController extends BackendController
{
    use ResourceController {
        getDataForForm as DataForForm;
        parseDataForSave as DataForSave;
    }

    protected string $viewPrefix = 'crawler::page';

    public function getBreadcrumbPrefix(...$params): void
    {
        $this->addBreadcrumb(
            [
                'title' => 'Websites',
                'url' => route('admin.crawler.websites.index'),
            ]
        );
    }

    protected function getDataForForm($model, ...$params): array
    {
        $data = $this->DataForForm($model, ...$params);
        $data['types'] = HookAction::getPostTypes();
        $data['languages'] = config('locales');
        $data['taxonomies'] = Taxonomy::with(['recursiveChildren'])
            ->whereNull('parent_id')
            ->where(['post_type' => 'posts', 'taxonomy' => 'categories'])
            ->get();
        return $data;
    }

    protected function parseDataForSave(array $attributes, ...$params): array
    {
        $data = $this->DataForSave($attributes, ...$params);

        if (empty($data['active'])) {
            $data['active'] = 0;
        }

        $data['url_hash'] = sha1($data['url']);
        $data['website_id'] = $params[0];
        $data['category_ids'] = Arr::get($data, 'category_ids', []);

        if ($maxPage = Arr::get($data, 'max_page')) {
            $data['next_page'] = $maxPage;
        }

        return $data;
    }

    protected function getDataTable(...$params): CrawlerPageDatatable
    {
        $dataTable = new CrawlerPageDatatable();

        $dataTable->mountData($params[0]);

        return $dataTable;
    }

    protected function validator(array $attributes, ...$params): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make(
            $attributes,
            [
                'url' => [
                    'required',
                    'url',
                ],
                'url_with_page' => [
                    'nullable',
                    'string',
                ],
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
