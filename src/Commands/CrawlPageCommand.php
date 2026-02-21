<?php

namespace Juzaweb\Modules\Crawler\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Juzaweb\Modules\Crawler\Elements\ArrayStringElement;
use Juzaweb\Modules\Crawler\Facades\Crawler;
use Juzaweb\Modules\Crawler\Link;
use Juzaweb\Modules\Crawler\Models\CrawlerPage;
use Symfony\Component\Console\Input\InputOption;

class CrawlPageCommand extends Command
{
    protected $name = 'crawl:pages';

    protected $description = 'Crawl pages.';

    public function handle(): int
    {
        $limit = $this->option('limit');

        $links = collect();
        $pages = CrawlerPage::with(['source'])
            ->join('crawler_sources', 'crawler_sources.id', '=', 'crawler_pages.source_id')
            ->where('crawler_sources.active', 1)
            ->where('crawler_pages.active', true)
            ->where(
                fn (Builder $q) => $q->where(
                    fn ($q2) => $q2->where(
                        fn ($q3) => $q3->whereNull('crawler_pages.url_with_page')
                            ->orWhere('crawler_pages.next_page', '<=', 1)
                    )
                        ->where('crawled_at', '<=', now()->subHours(2))
                )
                    ->orWhere(
                        fn ($q2) => $q2->whereNotNull('crawler_pages.url_with_page')
                            ->where('crawler_pages.next_page', '>', 1)
                            ->where('crawled_at', '<=', now()->subMinutes(10))
                    )
            )
            ->limit($limit)
            ->get(['crawler_pages.*']);

        if ($pages->isEmpty()) {
            $this->info('No pages to crawl.');
            return self::SUCCESS;
        }

        $pages->each(function (CrawlerPage $page) use ($links) {
            $link = new Link(
                $page->getCurrentPageUrl(),
                [
                    'links' => new ArrayStringElement(
                        $page->source->getLinkElement(),
                        'href'
                    )
                ]
            );
            $links->push($link);
        });

        $links = Crawler::crawl($links)->getResult();

        $inserts = collect();
        foreach ($links as $page => $link) {
            $page = $pages[$page];
            if (isset($link['error'])) {
                continue;
            }

            $inserts = $inserts->push(
                ...collect($link['links'])
                    ->map(
                        function ($link) use ($page) {
                            $link = get_full_url($link, $page->url);

                            return [
                                'url' => $link,
                                'url_hash' => sha1($link),
                                'source_id' => $page->source_id,
                                'page_id' => $page->id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    )
            );
        }

        $exists = DB::table('crawler_logs')
            ->whereIn('url_hash', $inserts->pluck('url_hash'))
            ->get(['url_hash'])
            ->pluck('url_hash')
            ->unique();

        $inserts = $inserts->whereNotIn('url_hash', $exists)->unique('url_hash');

        DB::transaction(
            function () use ($inserts, $pages) {
                DB::table('crawler_pages')
                    ->whereIn('id', $pages->pluck('id')->toArray())
                    ->update(['crawled_at' => now()]);
                DB::table('crawler_logs')->insert($inserts->toArray());

                $pages->each(function (CrawlerPage $p) {
                    if ($p->next_page > 1) {
                        $p->update(['next_page' => $p->next_page - 1]);
                    }
                });
            }
        );

        $this->info("{$inserts->count()} links crawled.");

        return self::SUCCESS;
    }

    protected function getOptions(): array
    {
        return [
            ['limit', null, InputOption::VALUE_OPTIONAL, 'Limit page crawling.', 10],
        ];
    }
}
