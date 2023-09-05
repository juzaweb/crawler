<?php

namespace Juzaweb\Crawler\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Juzaweb\CMS\Abstracts\DataTable;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\CMS\Traits\ResourceController;
use Juzaweb\Crawler\Http\Datatables\CrawlerContentDatatable;
use Juzaweb\Crawler\Jobs\TranslateCrawlerContentJob;
use Juzaweb\Crawler\Models\CrawlerContent;
use Juzaweb\Crawler\Models\CrawlerWebsite;

class ContentController extends BackendController
{
    use ResourceController;

    protected string $viewPrefix = 'crawler::content';
    protected CrawlerWebsite $website;

    public function reGet(Request $request, $websiteId, $id): JsonResponse|RedirectResponse
    {
        $content = CrawlerContent::find($id);

        DB::beginTransaction();
        try {
            $content->reget(true);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->success('Re-get Content success');
    }

    public function reTranslate(Request $request, $websiteId, $id): JsonResponse|RedirectResponse
    {
        $content = CrawlerContent::find($id);

        $content->retrans();

        return $this->success('Re-tranlating...');
    }

    protected function getBreadcrumbPrefix(...$params)
    {
        $this->addBreadcrumb(
            [
                'title' => 'Websites',
                'url' => route('admin.crawler.websites.index'),
            ]
        );
    }

    protected function getDataTable(...$params): DataTable
    {
        $dataTable = new CrawlerContentDatatable();
        $dataTable->mountData($params[0]);
        return $dataTable;
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
        return CrawlerContent::class;
    }

    protected function getTitle(...$params): string
    {
        return trans('crawler::content.crawler_contents');
    }
}
