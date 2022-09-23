<?php

namespace Juzaweb\Crawler\Commands;

use Illuminate\Console\Command;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerPage;

class AutoLinkCrawlerCommand extends Command
{
    protected $name = 'crawler:links';

    protected $description = 'Craw links command.';

    public function handle()
    {
        $pages = CrawlerPage::with(['website.template'])
            ->where(['active' => 1])
            ->whereHas(
                'website',
                function ($q) {
                    $q->where(['active' => 1]);
                }
            )
            ->inRandomOrder()
            ->limit(5)
            ->get();

        foreach ($pages as $page) {
            try {
                $craw = app(CrawlerContract::class)->crawPageLinks($page);

                if ($craw) {
                    $next_page = ($page->url_page && $page->next_page > 0)
                        ? $page->next_page + 1
                        : ($page->next_page > 0 ? 1 : 0);

                    $page->update(
                        [
                            'crawler_date' => now(),
                            'next_page' => $next_page,
                        ]
                    );
                }
            } catch (\Exception $e) {
                report($e);

                $page->update(
                    [
                        'crawler_date' => now(),
                        'error' => $e->getMessage(),
                    ]
                );
            }
        }
    }
}
