<?php

namespace Juzaweb\Crawler\Http\Controllers;

use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\Crawler\Http\Datatables\LinkDatatable;
use Juzaweb\Crawler\Models\CrawlerTemplate;

class LinkController extends BackendController
{
    public function index($templateId)
    {
        $this->addBreadcrumb(
            [
                'title' => trans('crawler::content.templates'),
                'url' => action([TemplateController::class, 'index'])
            ]
        );

        $title = trans('crawler::content.links');
        $template = CrawlerTemplate::findOrFail($templateId);
        $dataTable = $this->getDataTable($templateId);

        return view(
            'crawler::link.index',
            compact(
                'template',
                'dataTable',
                'title'
            )
        );
    }

    protected function getDataTable($templateId)
    {
        $dataTable = new LinkDatatable();
        $dataTable->mountData($templateId);
        return $dataTable;
    }
}
