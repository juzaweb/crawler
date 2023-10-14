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
use Juzaweb\Crawler\Support\Templates\DatabaseTemplate;

class WebsiteController extends PageController
{
    use ResourceController {
        getDataForForm as DataForForm;
        parseDataForSave as DataForSave;
    }

    protected string $viewPrefix = 'crawler::website';

    protected function afterSave($data, $model, ...$params): void
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
        $data['templateOptions'] = $data['templates']->mapWithKeys(
            function ($item, $key) {
                if (is_numeric($key)) {
                    return [$key => $item['name']];
                }

                return [
                    $item['class'] => $item['name']
                ];
            }
        );
        $data['queueOptions'] = [
            CrawlerWebsite::QUEUE_DEFAULT => __('Default'),
            CrawlerWebsite::QUEUE_HIGH => __('High'),
        ];
        return $data;
    }

    protected function parseDataForSave(array $attributes, ...$params): array
    {
        $data = $this->DataForSave($attributes, ...$params);
        $data['translate_replaces'] = array_values($data['translate_replaces'] ?? []);
        if (is_numeric($data['template_class'])) {
            $data['template_id'] = $data['template_class'];
        }

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
