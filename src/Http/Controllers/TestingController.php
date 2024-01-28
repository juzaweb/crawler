<?php

namespace Juzaweb\Crawler\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Juzaweb\CMS\Facades\HookAction;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerTemplate;
use Juzaweb\Crawler\Support\Templates\DatabaseTemplate;

class TestingController extends BackendController
{
    public function index(): View
    {
        $title = 'Testing';
        $templateOptions = HookAction::getCrawlerTemplates()->mapWithKeys(
            function ($item, $key) {
                if (is_numeric($key)) {
                    return [$key => $item['name']];
                }

                return [
                    $item['class'] => $item['name']
                ];
            }
        );
        $testingData = session()->get('crawler_testing_data');

        return view(
            'crawler::testing.index',
            compact('title', 'templateOptions', 'testingData')
        );
    }

    public function test(Request $request): JsonResponse|RedirectResponse
    {
        $url = $request->input('url');
        $template = $request->input('template');
        $option = $request->input('option');

        session()->put('crawler_testing_data', $request->all());

        if (is_numeric($template)) {
            $template = app()->make(
                DatabaseTemplate::class,
                [
                    'template' => CrawlerTemplate::findOrFail($template),
                ]
            );
        } else {
            $template = app($template);
        }

        switch ($option) {
            case 'link':
                $results = app(CrawlerContract::class)->crawLinksUrl(
                    $url,
                    $template
                );
                break;
            case 'content':
                $results = app(CrawlerContract::class)->crawContentUrl(
                    $url,
                    $template
                );
                break;
            case 'resource_link':
                $results = app(CrawlerContract::class)->crawLinksUrl(
                    $url,
                    $template,
                    true
                );
                break;
            case 'resource_content':
                $results = app(CrawlerContract::class)->getContensOfResource(
                    $url,
                    $template
                );
                break;
        }

        $view = match ($option) {
            'link', 'resource_link' => 'crawler::testing.components.link_result',
            'content', 'resource_content' => 'crawler::testing.components.content_result',
        };

        return $this->success(
            [
                'html' => view($view, compact('results'))
                    ->render(),
                'message' => 'Crawler success',
            ]
        );
    }
}
