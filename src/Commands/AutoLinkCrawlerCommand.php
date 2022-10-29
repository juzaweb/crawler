<?php

namespace Juzaweb\Crawler\Commands;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerPage;
use Symfony\Component\Console\Input\InputOption;

class AutoLinkCrawlerCommand extends Command
{
    protected $name = 'crawler:links';

    protected $description = 'Craw links command.';

    public function handle(): int
    {
        $query = CrawlerPage::with(['website.template'])
            ->where(['active' => 1])
            ->whereHas(
                'website',
                function ($q) {
                    $q->where(['active' => 1]);
                }
            );

        $pages = $query->orderBy('crawler_date', 'ASC')
            ->limit($this->option('limit'))
            ->get();

        foreach ($pages as $page) {
            try {
                $this->info("Craw {$page->url} in process...");

                $crawl = app(CrawlerContract::class)->crawPageLinks($page);

                $nextPage = $this->getNextPage($page, $crawl);

                $page->update(
                    [
                        'crawler_date' => now(),
                        'next_page' => $nextPage,
                    ]
                );

                $this->info("Crawed successful {$crawl} links - Next crawl page {$nextPage}");
            } catch (RequestException $e) {
                report($e);

                if ($e->hasResponse()) {
                    if ($e->getResponse()->getStatusCode() == '404') {
                        $page->update(
                            [
                                'crawler_date' => now(),
                                'error' => $e->getMessage(),
                                'next_page' => 1,
                            ]
                        );
                    }
                } else {
                    $page->update(
                        [
                            'crawler_date' => now(),
                            'error' => $e->getMessage(),
                        ]
                    );
                }

                $this->error($e->getMessage());
            } catch (\Exception $e) {
                report($e);

                $page->update(
                    [
                        'crawler_date' => now(),
                        'error' => $e->getMessage(),
                    ]
                );

                $this->error($e->getMessage());
            }

            sleep($this->option('sleep'));
        }

        return self::SUCCESS;
    }

    protected function getNextPage(CrawlerPage $page, int $crawl): int
    {
        if ($crawl == 0 && $page->next_page > 0) {
            return 1;
        }

        return ($page->url_with_page && $page->next_page > 0)
            ? $page->next_page + 1
            : ($page->next_page > 0 ? 1 : 0);
    }

    protected function getOptions(): array
    {
        return [
            ['limit', null, InputOption::VALUE_OPTIONAL, 'The limit rows crawl per run.', 5],
            ['sleep', null, InputOption::VALUE_OPTIONAL, 'Sleep seconds per crawl.', 2],
        ];
    }
}
