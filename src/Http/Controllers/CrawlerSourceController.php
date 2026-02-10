<?php

namespace Juzaweb\Modules\Crawler\Http\Controllers;

use Juzaweb\Modules\Core\Facades\Breadcrumb;
use Juzaweb\Modules\Core\Http\Controllers\AdminController;
use Illuminate\Support\Facades\DB;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;
use Juzaweb\Modules\Crawler\Http\Requests\CrawlerSourceRequest;
use Juzaweb\Modules\Crawler\Http\Requests\CrawlerSourceActionsRequest;
use Juzaweb\Modules\Crawler\Http\DataTables\CrawlerSourcesDataTable;

class CrawlerSourceController extends AdminController
{
    public function index(CrawlerSourcesDataTable $dataTable)
    {
        Breadcrumb::add(__('Crawler Sources'));

        $createUrl = action([static::class, 'create']);

        return $dataTable->render(
            'crawler::crawler-source.index',
            compact('createUrl')
        );
    }

    public function create()
    {
        Breadcrumb::add(__('Crawler Sources'), admin_url('crawlersources'));

        Breadcrumb::add(__('Create Crawler Source'));

        $backUrl = action([static::class, 'index']);

        return view(
            'crawler::crawler-source.form',
            [
                'model' => new CrawlerSource(),
                'action' => action([static::class, 'store']),
                'backUrl' => $backUrl,
            ]
        );
    }

    public function edit(string $id)
    {
        Breadcrumb::add(__('Crawler Sources'), admin_url('crawlersources'));

        Breadcrumb::add(__('Create Crawler Sources'));

        $model = CrawlerSource::findOrFail($id);
        $backUrl = action([static::class, 'index']);

        return view(
            'crawler::crawler-source.form',
            [
                'action' => action([static::class, 'update'], [$id]),
                'model' => $model,
                'backUrl' => $backUrl,
            ]
        );
    }

    public function store(CrawlerSourceRequest $request)
    {
        $model = DB::transaction(
            function () use ($request) {
                $data = $request->validated();

                return CrawlerSource::create($data);
            }
        );

        return $this->success([
            'redirect' => action([static::class, 'index']),
            'message' => __('Source :name created successfully', ['name' => $model->name]),
        ]);
    }

    public function update(CrawlerSourceRequest $request, string $id)
    {
        $model = CrawlerSource::findOrFail($id);

        $model = DB::transaction(
            function () use ($request, $model) {
                $data = $request->validated();

                $model->update($data);

                return $model;
            }
        );

        return $this->success([
            'redirect' => action([static::class, 'index']),
            'message' => __('CrawlerSource :name updated successfully', ['name' => $model->name]),
        ]);
    }

    public function bulk(CrawlerSourceActionsRequest $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        $models = CrawlerSource::whereIn('id', $ids)->get();

        foreach ($models as $model) {
            if ($action === 'activate') {
                $model->update(['active' => true]);
            }

            if ($action === 'deactivate') {
                $model->update(['active' => false]);
            }

            if ($action === 'delete') {
                $model->delete();
            }
        }

        return $this->success([
            'message' => __('Bulk action performed successfully'),
        ]);
    }
}
