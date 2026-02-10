<?php

namespace Juzaweb\Modules\Crawler\Http\Controllers;

use Juzaweb\Modules\Core\Facades\Breadcrumb;
use Juzaweb\Modules\Core\Http\Controllers\AdminController;
use Illuminate\Support\Facades\DB;
use Juzaweb\Modules\Crawler\Models\CrawlerPage;
use Juzaweb\Modules\Crawler\Http\Requests\CrawlerPageRequest;
use Juzaweb\Modules\Crawler\Http\Requests\CrawlerPageActionsRequest;
use Juzaweb\Modules\Crawler\Http\DataTables\CrawlerPagesDataTable;

class CrawlerPageController extends AdminController
{
    public function index(CrawlerPagesDataTable $dataTable, string $sourceId)
    {
        Breadcrumb::add(__('Crawler Pages'));

        $createUrl = action([static::class, 'create'], [$sourceId]);

        return $dataTable
            ->setSourceId($sourceId)
            ->render(
                'crawler::crawler-page.index',
                compact('createUrl')
            );
    }

    public function create(string $sourceId)
    {
        Breadcrumb::add(__('Crawler Pages'), action([static::class, 'index'], [$sourceId]));

        Breadcrumb::add(__('Create Crawler Page'));

        $backUrl = action([static::class, 'index'], [$sourceId]);
        $model = new CrawlerPage(['source_id' => $sourceId]);

        return view(
            'crawler::crawler-page.form',
            [
                'model' => $model,
                'action' => action([static::class, 'store'], [$sourceId]),
                'backUrl' => $backUrl,
            ]
        );
    }

    public function edit(string $sourceId, string $id)
    {
        $model = CrawlerPage::where('source_id', $sourceId)->findOrFail($id);

        Breadcrumb::add(__('Crawler Pages'), action([static::class, 'index'], [$sourceId]));

        Breadcrumb::add(__('Edit Crawler Page'));

        $backUrl = action([static::class, 'index'], [$sourceId]);

        return view(
            'crawler::crawler-page.form',
            [
                'action' => action([static::class, 'update'], [$sourceId, $id]),
                'model' => $model,
                'backUrl' => $backUrl,
            ]
        );
    }

    public function store(CrawlerPageRequest $request, string $sourceId)
    {
        $model = DB::transaction(
            function () use ($request, $sourceId) {
                $data = $request->validated();
                $data['source_id'] = $sourceId;

                return CrawlerPage::create($data);
            }
        );

        return $this->success([
            'redirect' => action([static::class, 'index'], [$sourceId]),
            'message' => __('CrawlerPage created successfully'),
        ]);
    }

    public function update(CrawlerPageRequest $request, string $sourceId, string $id)
    {
        $model = CrawlerPage::where('source_id', $sourceId)->findOrFail($id);

        $model = DB::transaction(
            function () use ($request, $model) {
                $data = $request->validated();

                $model->update($data);

                return $model;
            }
        );

        return $this->success([
            'redirect' => action([static::class, 'index'], [$sourceId]),
            'message' => __('CrawlerPage updated successfully'),
        ]);
    }

    public function bulk(CrawlerPageActionsRequest $request, string $sourceId)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        $models = CrawlerPage::where('source_id', $sourceId)
            ->whereIn('id', $ids)
            ->get();

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
