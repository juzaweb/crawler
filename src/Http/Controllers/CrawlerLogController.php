<?php

namespace Juzaweb\Modules\Crawler\Http\Controllers;

use Juzaweb\Modules\Core\Facades\Breadcrumb;
use Juzaweb\Modules\Core\Http\Controllers\AdminController;
use Juzaweb\Modules\Crawler\Http\DataTables\CrawlerLogsDataTable;
use Juzaweb\Modules\Crawler\Http\Requests\CrawlerLogActionsRequest;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;

class CrawlerLogController extends AdminController
{
    public function index(CrawlerLogsDataTable $dataTable)
    {
        Breadcrumb::add(__('Crawler Logs'));

        return $dataTable->render('crawler::crawler-log.index');
    }

    public function bulk(CrawlerLogActionsRequest $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        $models = CrawlerLog::whereIn('id', $ids)->get();

        foreach ($models as $model) {
            if ($action === 'delete') {
                $model->delete();
            }
        }

        return $this->success([
            'message' => __('Bulk action performed successfully'),
        ]);
    }
}
