<?php

namespace Juzaweb\Crawler\Commands;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerPage;

class AutoLinkCrawlerCommand extends Command
{
    protected $name = 'crawler:links';

    protected $description = 'Craw links command.';

    public function handle(): int
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
                $this->info("Craw {$page->url} in process...");

                $craw = app(CrawlerContract::class)->crawPageLinks($page);

                $nextPage = ($page->url_with_page && $page->next_page > 0)
                    ? $page->next_page + 1
                    : ($page->next_page > 0 ? 1 : 0);

                $page->update(
                    [
                        'crawler_date' => now(),
                        'next_page' => $nextPage,
                    ]
                );

                $this->info("Crawed successful {$craw} links - Next crawl page {$nextPage}");
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
        }

        return self::SUCCESS;
    }
}
