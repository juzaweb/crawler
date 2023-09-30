<?php

namespace Juzaweb\Crawler\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Juzaweb\CMS\Facades\HookAction;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\Crawler\Contracts\CrawlerContract;

class TestingController extends BackendController
{
    public function index(): View
    {
        $title = 'Testing';
        $templates = HookAction::getCrawlerTemplates();
        $testingData = session()->get('crawler_testing_data');

        return view(
            'crawler::testing.index',
            compact('title', 'templates', 'testingData')
        );
    }

    public function test(Request $request): JsonResponse|RedirectResponse
    {
        $url = $request->input('url');
        $template = $request->input('template');
        $option = $request->input('option');

        session()->put('crawler_testing_data', $request->all());

        switch ($option) {
            case 'link':
                $results = app(CrawlerContract::class)->crawLinksUrl(
                    $url,
                    app($template)
                );
                break;
            case 'content':
                $results = app(CrawlerContract::class)->crawContentUrl(
                    $url,
                    app($template)
                );
                break;
        }

        return $this->success(
            [
                'html' => view("crawler::testing.components.{$option}_result", compact('results'))
                    ->render(),
                'message' => 'Crawler success',
            ]
        );
    }
}
