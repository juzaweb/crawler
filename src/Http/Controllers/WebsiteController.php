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
use Juzaweb\CMS\Facades\HookAction;
use Juzaweb\CMS\Traits\ResourceController;
use Juzaweb\Crawler\Http\Datatables\WebsiteDatatable;
use Juzaweb\Crawler\Models\CrawlerWebsite;

class WebsiteController extends PageController
{
    use ResourceController {
        getDataForForm as DataForForm;
    }

    protected string $viewPrefix = 'crawler::website';

    protected function getDataForForm($model, ...$params)
    {
        $data = $this->DataForForm($model, ...$params);
        $data['templates'] = HookAction::getCrawlerTemplates();
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
