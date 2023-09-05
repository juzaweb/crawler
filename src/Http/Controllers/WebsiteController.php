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

use Juzaweb\Backend\Http\Controllers\Backend\PageController;
use Juzaweb\Backend\Models\Taxonomy;
use Juzaweb\CMS\Facades\HookAction;
use Juzaweb\CMS\Traits\ResourceController;
use Juzaweb\Crawler\Http\Datatables\WebsiteDatatable;
use Juzaweb\Crawler\Jobs\ReplaceTranslateJob;
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
        if ($model->wasChanged('translate_replaces')) {
            ReplaceTranslateJob::dispatch($model);
        }
    }

    protected function getDataForForm($model, ...$params): array
    {
        $data = $this->DataForForm($model, ...$params);
        $data['templates'] = HookAction::getCrawlerTemplates();
        $data['types'] = HookAction::getPostTypes();
        return $data;
    }

    protected function parseDataForSave(array $attributes, ...$params): array
    {
        $data = $this->DataForSave($attributes, ...$params);
        $data['translate_replaces'] = array_values($data['translate_replaces'] ?? []);

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
