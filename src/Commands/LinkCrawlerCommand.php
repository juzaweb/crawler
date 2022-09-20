<?php

namespace Juzaweb\Crawler\Commands;

use Illuminate\Console\Command;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerPage;

class LinkCrawlerCommand extends Command
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
            app(CrawlerContract::class)->crawLinks($page);
        }
    }
}
