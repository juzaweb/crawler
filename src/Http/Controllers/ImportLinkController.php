<?php

namespace Juzaweb\Crawler\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerPage;

class ImportLinkController extends BackendController
{
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $title = trans('Import links');
        $importData = session()->get('crawler_import_data');
        $pages = CrawlerPage::where(['active' => true])->get();

        return view(
            'crawler::import-link.index',
            compact('title', 'pages', 'importData')
        );
    }

    public function import(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $page = CrawlerPage::findOrFail($request->post('page'));
        session()->put('crawler_import_data', $request->all());

        $url = $request->post('url');
        $search = $request->post('search');
        $contents = $this->getClient()->get($url)->getBody()->getContents();

        $html = str_get_html($contents);

        $links = [];
        foreach ($html->find('a') as $e) {
            $href = trim(get_full_url($e->href, $url));
            if (preg_match("/{$search}/i", $href)) {
                $links[] = $href;
            }
        }

        $inserts = [];
        if ($request->post('save')) {
            $inserts = app(CrawlerContract::class)->checkAndInsertLinks($links, $page);
        }

        return $this->success(['links' => $links, 'inserts' => count($inserts)]);
    }

    protected function getClient(): Client
    {
        return new Client(['timeout' => 10]);
    }
}
