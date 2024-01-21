<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Juzaweb\CMS\Contracts\HookActionContract;
use Juzaweb\CMS\Contracts\PostImporterContract;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Exceptions\CrawlerException;
use Juzaweb\Crawler\Http\Requests\ImportRequest;

class ImportController extends BackendController
{
    public function __construct(
        protected CrawlerContract $crawler,
        protected PostImporterContract $postImporter,
        protected HookActionContract $hookAction
    ) {
    }

    public function import(ImportRequest $request): JsonResponse|RedirectResponse
    {
        $template = $request->input('template');
        $url = $request->input('url');
        $type = $request->input('type');

        try {
            $post = DB::transaction(
                function () use ($template, $url, $request, $type) {
                    $data = app(CrawlerContract::class)->crawContentUrl(
                        $url,
                        app($template)
                    );

                    if (empty($data['title']) && empty($data['content'])) {
                        throw new CrawlerException("Cannot import get title and content url: {$url}");
                    }

                    $data['type'] = $type;
                    $taxonomies = $this->hookAction->getTaxonomies($type)->keys();
                    $taxonomies = $request->only($taxonomies->toArray());

                    foreach ($taxonomies as $taxonomy => $value) {
                        $data[$taxonomy] = $value;
                    }

                    return $this->postImporter->import($data);
                }
            );
        } catch (CrawlerException $e) {
            return $this->error($e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return $this->error("Cannot import url: {$url}");
        }

        return $this->success(
            [
                'message' => __('Crawler: :name imported successfully', ['name' => $post->title]),
                //'redirect' => route('admin.posts.index', [$post->type]),
            ]
        );
    }

    public function importWithPage()
    {
        
    }
}
