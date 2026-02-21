<?php

namespace Juzaweb\Modules\Crawler\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Juzaweb\Modules\Core\Facades\Breadcrumb;
use Juzaweb\Modules\Core\Http\Controllers\AdminController;
use Illuminate\Support\Facades\DB;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;
use Juzaweb\Modules\Crawler\Http\Requests\CrawlerSourceRequest;
use Juzaweb\Modules\Crawler\Http\Requests\CrawlerSourceActionsRequest;
use Juzaweb\Modules\Crawler\Http\DataTables\CrawlerSourcesDataTable;
use Juzaweb\Modules\Crawler\Contracts\Crawler;

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

    public function create(Crawler $crawler)
    {
        Breadcrumb::add(__('Crawler Sources'), admin_url('crawler-sources'));

        Breadcrumb::add(__('Create Crawler Source'));

        $backUrl = action([static::class, 'index']);

        return view(
            'crawler::crawler-source.form',
            [
                'model' => new CrawlerSource(),
                'action' => action([static::class, 'store']),
                'backUrl' => $backUrl,
                'dataTypes' => array_merge(
                    ['' => __('Select Data Type')],
                    $crawler->getDataTypes()
                ),
            ]
        );
    }

    public function edit(Crawler $crawler, string $id)
    {
        Breadcrumb::add(__('Crawler Sources'), admin_url('crawler-sources'));

        Breadcrumb::add(__('Create Crawler Sources'));

        $model = CrawlerSource::findOrFail($id);
        $backUrl = action([static::class, 'index']);

        return view(
            'crawler::crawler-source.form',
            [
                'action' => action([static::class, 'update'], [$id]),
                'model' => $model,
                'backUrl' => $backUrl,
                'dataTypes' => array_merge(
                    ['' => __('Select Data Type')],
                    $crawler->getDataTypes()
                ),
            ]
        );
    }

    public function store(CrawlerSourceRequest $request)
    {
        $model = DB::transaction(
            function () use ($request) {
                $data = $request->validated();
                $pages = $data['crawler_pages'] ?? [];
                unset($data['crawler_pages']);

                $model = CrawlerSource::create($data);

                $this->syncCrawlerPages($model, $pages);

                return $model;
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
                $pages = $data['crawler_pages'] ?? [];
                unset($data['crawler_pages']);

                $model->update($data);

                $this->syncCrawlerPages($model, $pages);

                return $model;
            }
        );

        return $this->success([
            'redirect' => action([static::class, 'index']),
            'message' => __('CrawlerSource :name updated successfully', ['name' => $model->name]),
        ]);
    }

    private function syncCrawlerPages(CrawlerSource $model, array $pages): void
    {
        $requestIds = collect($pages)->pluck('id')->filter()->toArray();

        $model->pages()->whereNotIn('id', $requestIds)->delete();

        foreach ($pages as $page) {
            $model->pages()->updateOrCreate(
                ['id' => $page['id'] ?? null],
                [
                    'url' => $page['url'],
                    'url_with_page' => $page['url_with_page'] ?? null,
                    'active' => $page['active'] ?? 0,
                ]
            );
        }
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

    public function getComponents(Request $request, Crawler $crawler): JsonResponse
    {
        $key = $request->input('data_type');
        $dataType = $crawler->getDataType($key);

        if (!$dataType) {
            return response()->json(['html' => '']);
        }

        $components = collect($dataType->components())
            ->map(
                function ($item, $key) {
                    return (object) [
                        'name' => $key,
                        'element' => '',
                        'format' => $item['type'] ?? 'text',
                    ];
                }
            );

        $html = view(
            'crawler::crawler-source.components.items',
            ['items' => $components]
        )->render();

        return response()->json(['html' => $html]);
    }
}
