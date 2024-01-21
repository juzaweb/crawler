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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Juzaweb\CMS\Contracts\HookActionContract;
use Juzaweb\CMS\Contracts\PostImporterContract;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\Crawler\Contracts\CrawlerContract;
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

        $post = DB::transaction(
            function () use ($template, $url) {
                $data = app(CrawlerContract::class)->crawContentUrl(
                    $url,
                    app($template)
                );

                $taxonomies = $this->hookAction->getTaxonomies($data['type']);

                foreach ($taxonomies as $key => $taxonomy) {
                    if ($key != 'tags') {
                        continue;
                    }

                    if ($taxs = Arr::get($data, $key)) {
                        if (empty($data[$key])) {
                            unset($data[$key]);
                            continue;
                        }

                        $data[$key] = [];
                        foreach ($taxs as $tax) {
                            $data[$key][] = ['name' => $tax, 'slug' => Str::slug($tax)];
                        }
                    }
                }

                return $this->postImporter->import($data);
            }
        );

        return $this->success(
            [
                'message' => __('Crawler: :name imported successfully', ['name' => $post->title]),
                'redirect' => route('admin.posts.edit', [$post->type, $post->id]),
            ]
        );
    }
}
