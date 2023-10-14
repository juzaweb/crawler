<?php

namespace Juzaweb\Crawler\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\Crawler\Models\CrawlerPage;
use Juzaweb\Crawler\Models\CrawlerWebsite;

class ImportPageController extends BackendController
{
    public function index(): Factory|View
    {
        $title = trans('Import links');
        $importData = session()->get('crawler_import_page_data');
        $websites = CrawlerWebsite::get();

        return view(
            'crawler::import-page.index',
            compact('title', 'websites', 'importData')
        );
    }

    public function find(Request $request): JsonResponse|RedirectResponse
    {
        session()->put('crawler_import_page_data', $request->all());

        $url = $request->post('url');
        $search = $request->post('search');
        $contents = $this->getClient()->get($url)->getBody()->getContents();

        $html = str_get_html($contents);

        $links = [];
        foreach ($html->find('a') as $e) {
            $href = trim(get_full_url($e->href, $url));
            if (preg_match("/{$search}/i", $href)) {
                $links[] = [
                    'href' => $href,
                    'text' => $e->plaintext,
                    'url_hash' => sha1($href),
                ];
            }
        }

        $links = collect($links)
            ->filter(fn($item) => !empty($item['href']))
            ->unique('url_hash');

        $exists = CrawlerPage::whereIn('url_hash', $links->pluck('url_hash'))
            ->get(['url_hash'])
            ->pluck('url_hash');

        return $this->success(['links' => $links->whereNotIn('url_hash', $exists)->all()]);
    }

    public function import(Request $request): JsonResponse|RedirectResponse
    {
        $website = CrawlerWebsite::findOrFail($request->input('website'));
        $hrefs = $request->input('href', []);
        $import = 0;

        foreach ($hrefs as $index => $href) {
            if (empty($request->input("categories.{$index}"))) {
                continue;
            }

            if (empty($request->input("max_page.{$index}"))) {
                continue;
            }

            $dataElements = $website->getTemplateClass()->getDataElements();

            if (empty($dataElements['page_suffix'])) {
                continue;
            }

            $page = new CrawlerPage();
            $page->website_id = $website->id;
            $page->category_ids = ['categories' => [$request->input("categories.{$index}")]];
            $page->url = $href;
            $page->url_hash = sha1($href);
            $page->url_with_page = $href.$dataElements['page_suffix'];
            $page->max_page = (int) $request->input("max_page.{$index}");
            $page->next_page = $page->max_page;
            $page->active = (bool) $request->input("active.{$index}");
            $page->save();

            $import++;
        }

        return $this->success(['import' => $import, 'message' => 'Import successfully!']);
    }

    public function getWebsiteInfo(Request $request): JsonResponse
    {
        $website = CrawlerWebsite::findOrFail($request->input('website'));

        return $this->success(
            [
                'website' => $website,
                'template' => $website->getTemplateClass()->getDataElements(),
            ]
        );
    }

    protected function getClient(): Client
    {
        return new Client(['timeout' => 10, 'connect_timeout' => 10]);
    }
}
