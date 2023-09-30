<?php

namespace Juzaweb\Crawler\Commands\Crawler;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Juzaweb\Crawler\Jobs\CrawlerLinksJob;
use Juzaweb\Crawler\Models\CrawlerPage;
use Symfony\Component\Console\Input\InputOption;

class AutoLinkCrawlerCommand extends Command
{
    protected $name = 'crawler:links';

    protected $description = 'Craw links command.';

    protected int $limitCrawTimesPerPage = 10;

    public function handle(): int
    {
        if (!get_config('crawler_enable')) {
            return self::SUCCESS;
        }

        $pages = $this->getPages();
        $queue = config('crawler.queue.crawler');

        $job = 0;
        foreach ($pages as $page) {
            $nextPage = $page->next_page;
            $totalPages = min($this->limitCrawTimesPerPage, $nextPage);

            for ($times = 1; $times <= $totalPages; $times++) {
                if ($times > $nextPage) {
                    break;
                }

                CrawlerLinksJob::dispatch($page, $nextPage)->onQueue($queue)
                    ->delay(Carbon::now()->addSeconds($job * 10));

                $this->info("Craw {$page->url} in process...");

                if ($page->max_page) {
                    $nextPage--;
                } else {
                    $nextPage++;
                }

                $job++;
            }

            if ($nextPage < 1) {
                $nextPage = 1;
            }

            $page->update(
                [
                    'crawler_date' => now(),
                    'next_page' => $nextPage,
                ]
            );
        }

        return self::SUCCESS;
    }

    protected function getPages(): Collection
    {
        $query = CrawlerPage::with(['website.template'])
            ->joinRelationship('website')
            ->where(['crawler_pages.active' => true, 'crawler_websites.active' => true]);

        if ($this->option('is_resource')) {
            $query->where(['is_resource_page' => 1]);
        }

        return $query->orderBy('next_page', 'DESC')
            ->orderBy('crawler_date', 'ASC')
            ->limit($this->option('limit'))
            ->get();
    }

    protected function getNextPage(CrawlerPage $page): int
    {
        if ($page->max_page) {
            if ($page->url_with_page && $page->next_page > 1) {
                return $page->next_page - 1;
            }

            return $page->next_page > 0 ? 1 : 0;
        }

        if ($page->url_with_page && $page->next_page > 0) {
            return $page->next_page + 1;
        }

        return $page->next_page > 0 ? 1 : 0;
    }

    protected function getOptions(): array
    {
        return [
            ['limit', null, InputOption::VALUE_OPTIONAL, 'The limit rows crawl per run.', 5],
            ['is_resource', null, InputOption::VALUE_OPTIONAL, 'Sleep seconds per crawl.', false],
        ];
    }
}
