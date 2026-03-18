<?php

namespace Juzaweb\Modules\Crawler\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Juzaweb\Modules\Core\Facades\Breadcrumb;
use Juzaweb\Modules\Core\Http\Controllers\AdminController;
use Juzaweb\Modules\Crawler\Contracts\Crawler;
use Juzaweb\Modules\Crawler\Http\DataTables\CrawlerSourcesDataTable;
use Juzaweb\Modules\Crawler\Http\Requests\CrawlerSourceActionsRequest;
use Juzaweb\Modules\Crawler\Http\Requests\CrawlerSourceRequest;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;

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
        $locales = config('locales');

        return view(
            'crawler::crawler-source.form',
            [
                'model' => new CrawlerSource,
                'action' => action([static::class, 'store']),
                'backUrl' => $backUrl,
                'dataTypes' => array_merge(
                    ['' => __('Select Data Type')],
                    $crawler->getDataTypes()
                ),
                'locales' => $locales,
            ]
        );
    }

    public function edit(Crawler $crawler, string $id)
    {
        Breadcrumb::add(__('Crawler Sources'), admin_url('crawler-sources'));

        Breadcrumb::add(__('Create Crawler Sources'));

        $model = CrawlerSource::findOrFail($id);
        $backUrl = action([static::class, 'index']);
        $locales = config('locales');

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
                'locales' => $locales,
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
                    'locale' => $page['locale'] ?? app()->getLocale(),
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

            if ($action === 'inactive') {
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

        if (! $dataType) {
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

    public function export()
    {
        $sources = CrawlerSource::with('pages')->get();
        $xml = new \SimpleXMLElement('<crawler_data/>');

        foreach ($sources as $source) {
            $sourceNode = $xml->addChild('source');
            $sourceNode->addChild('name', (string) $source->name);
            $sourceNode->addChild('active', (string) $source->active);
            $sourceNode->addChild('data_type', (string) $source->data_type);
            $sourceNode->addChild('link_element', (string) $source->link_element);
            $sourceNode->addChild('link_regex', (string) $source->link_regex);

            // Components
            $componentsNode = $sourceNode->addChild('components');
            foreach ($source->components ?? [] as $key => $component) {
                $compNode = $componentsNode->addChild('component');
                $compNode->addAttribute('key', (string) $key);
                $compNode->addChild('name', (string) ($component['name'] ?? ''));
                $compNode->addChild('element', (string) ($component['element'] ?? ''));
                $compNode->addChild('attr', is_array($component['attr'] ?? '') ? json_encode($component['attr']) : (string) ($component['attr'] ?? ''));
                $compNode->addChild('format', (string) ($component['format'] ?? ''));
            }

            // Removes
            $removesNode = $sourceNode->addChild('removes');
            foreach ($source->removes ?? [] as $remove) {
                if (is_array($remove)) {
                    $removeNode = $removesNode->addChild('remove');
                    $removeNode->addChild('element', (string) ($remove['element'] ?? ''));
                    $removeNode->addChild('index', (string) ($remove['index'] ?? ''));
                } else {
                    $removesNode->addChild('remove', (string) $remove);
                }
            }

            // Pages
            $pagesNode = $sourceNode->addChild('pages');
            foreach ($source->pages as $page) {
                $pageNode = $pagesNode->addChild('page');
                $pageNode->addChild('url', (string) $page->url);
                $pageNode->addChild('url_with_page', (string) $page->url_with_page);
                $pageNode->addChild('locale', (string) $page->locale);
                $pageNode->addChild('active', (string) $page->active);
            }
        }

        return response($xml->asXML(), 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="crawler-sources.xml"',
        ]);
    }

    public function importData(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xml']);

        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($request->file('file')->path());

        if ($xml === false) {
            libxml_clear_errors();

            return back()->withErrors(['file' => __('Invalid XML file')]);
        }

        DB::transaction(function () use ($xml) {
            foreach ($xml->source as $sourceNode) {
                $components = [];
                if (isset($sourceNode->components->component)) {
                    foreach ($sourceNode->components->component as $compNode) {
                        $key = (string) $compNode['key'];
                        $components[$key] = [
                            'name' => (string) $compNode->name,
                            'element' => (string) $compNode->element,
                            'attr' => (string) $compNode->attr,
                            'format' => (string) $compNode->format,
                        ];
                    }
                }

                $removes = [];
                if (isset($sourceNode->removes->remove)) {
                    foreach ($sourceNode->removes->remove as $removeNode) {
                        if (isset($removeNode->element) || isset($removeNode->index)) {
                            $removes[] = [
                                'element' => (string) ($removeNode->element ?? ''),
                                'index' => (string) ($removeNode->index ?? ''),
                            ];
                        } else {
                            $removes[] = (string) $removeNode;
                        }
                    }
                }

                $source = CrawlerSource::create([
                    'name' => (string) $sourceNode->name,
                    'active' => (int) $sourceNode->active,
                    'data_type' => (string) $sourceNode->data_type,
                    'link_element' => (string) $sourceNode->link_element,
                    'link_regex' => (string) $sourceNode->link_regex,
                    'components' => $components,
                    'removes' => $removes,
                ]);

                if (isset($sourceNode->pages->page)) {
                    foreach ($sourceNode->pages->page as $pageNode) {
                        $source->pages()->create([
                            'url' => (string) $pageNode->url,
                            'url_with_page' => (string) $pageNode->url_with_page,
                            'locale' => (string) $pageNode->locale,
                            'active' => (int) $pageNode->active,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.crawler-sources.index')
            ->with('success', __('Import successfully.'));
    }
}
