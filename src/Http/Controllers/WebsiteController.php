<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    MIT
 */

namespace Juzaweb\Crawler\Http\Controllers;

use Illuminate\Support\Arr;
use Juzaweb\Backend\Http\Controllers\Backend\PageController;
use Juzaweb\Backend\Models\Taxonomy;
use Juzaweb\CMS\Facades\HookAction;
use Juzaweb\CMS\Traits\ResourceController;
use Juzaweb\Crawler\Http\Datatables\WebsiteDatatable;
use Juzaweb\Crawler\Models\CrawlerPage;
use Juzaweb\Crawler\Models\CrawlerWebsite;

class WebsiteController extends PageController
{
    use ResourceController {
        getDataForForm as DataForForm;
        parseDataForSave as DataForSave;
    }

    protected string $viewPrefix = 'crawler::website';

    protected function afterSave($data, $model, ...$params)
    {
        $pages = [];
        foreach (Arr::get($data, 'pages', []) as $page) {
            $url = trim(Arr::get($page, 'url'));

            $pages[] = CrawlerPage::updateOrCreate(
                [
                    'id' => Arr::get($page, 'id'),
                ],
                [
                    'url' => $url,
                    'url_hash' => sha1($url),
                    'url_with_page' => Arr::get($page, 'url_with_page'),
                    'post_type' => Arr::get($page, 'post_type'),
                    'category_ids' => Arr::get($page, 'category_ids', []),
                    'active' => Arr::get($page, 'active', 0),
                    'website_id' => $model->id,
                ]
            )->id;
        }

        CrawlerPage::where('website_id', '=', $model->id)
            ->where('is_resource_page', '=', 0)
            ->whereNotIn('id', $pages)
            ->delete();
    }

    protected function getDataForForm($model, ...$params): array
    {
        $data = $this->DataForForm($model, ...$params);
        $data['templates'] = HookAction::getCrawlerTemplates();
        $data['types'] = HookAction::getPostTypes();
        $data['pages'] = $model->pages()->where(['is_resource_page' => 0])->get();
        $data['taxonomies'] = Taxonomy::where(['post_type' => 'posts'])->limit(10)->get();
        return $data;
    }

    protected function parseDataForSave(array $attributes, ...$params): array
    {
        $data = $this->DataForSave($attributes, ...$params);
        if (empty($data['active'])) {
            $data['active'] = 0;
        }
        return $data;
    }

    protected function getDataTable(...$params): WebsiteDatatable
    {
        return new WebsiteDatatable();
    }

    protected function validator(array $attributes, ...$params): array
    {
        return [
            'domain' => 'required|string',
            'has_ssl' => 'nullable|in:0,1'
        ];
    }

    protected function getModel(...$params): string
    {
        return CrawlerWebsite::class;
    }

    protected function getTitle(...$params): array|string|null
    {
        return trans('crawler::content.websites');
    }
}
