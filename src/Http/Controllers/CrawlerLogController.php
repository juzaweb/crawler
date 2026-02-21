<?php

namespace Juzaweb\Modules\Crawler\Http\Controllers;

use Juzaweb\Modules\Core\Facades\Breadcrumb;
use Juzaweb\Modules\Core\Http\Controllers\AdminController;
use Juzaweb\Modules\Crawler\Enums\CrawlerLogStatus;
use Juzaweb\Modules\Crawler\Http\DataTables\CrawlerLogsDataTable;
use Juzaweb\Modules\Crawler\Http\Requests\CrawlerLogActionsRequest;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;
use Juzaweb\Modules\Crawler\Models\CrawlerPage;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;

class CrawlerLogController extends AdminController
{
    public function index(CrawlerLogsDataTable $dataTable)
    {
        Breadcrumb::add(__('Crawler Logs'));

        $sources = CrawlerSource::pluck('name', 'id')->toArray();
        $pages = CrawlerPage::pluck('url', 'id')->toArray();
        $statuses = CrawlerLogStatus::cases();

        return $dataTable->render(
            'crawler::crawler-log.index',
            compact('sources', 'pages', 'statuses')
        );
    }

    public function edit(string $id)
    {
        $model = CrawlerLog::findOrFail($id);

        Breadcrumb::add(__('Crawler Logs'), admin_url('crawler-logs'));
        Breadcrumb::add(__('View Crawler Log'));

        return view('crawler::crawler-log.form', compact('model'));
    }

    public function bulk(CrawlerLogActionsRequest $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        $models = CrawlerLog::whereIn('id', $ids)->get();

        foreach ($models as $model) {
            if ($action === 'delete') {
                $model->delete();
            } elseif ($action === 'retry') {
                $model->update([
                    'status' => CrawlerLogStatus::PENDING,
                    'error' => null,
                ]);
            }
        }

        return $this->success([
            'message' => __('Bulk action performed successfully'),
        ]);
    }
}
