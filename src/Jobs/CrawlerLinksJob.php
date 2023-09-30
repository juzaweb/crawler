<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/juzacms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Jobs;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerPage;
use Juzaweb\Proxies\Contracts\ProxyManager;

class CrawlerLinksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;

    public function __construct(
        protected CrawlerPage $page,
        protected int $pageNumber
    ) {
    }

    public function handle(): void
    {
        $this->page->lockForUpdate();

        $fnCrawl = function () {
            $proxy = null;
            if ((bool) get_config('crawler_enable_proxy', 0)) {
                $proxy = app(ProxyManager::class)->random()?->toGuzzleHttpProxy();
            }

            $results = app(CrawlerContract::class)->crawPageLinks(
                $this->page,
                $this->pageNumber,
                $proxy
            );

            $this->page->touch();

            return $results;
        };

        try {
            DB::transaction($fnCrawl);

            // if ($crawl == 0) {
            //     if ($page->next_page == 1) {
            //         return 1;
            //     }
            //
            //     return $page->next_page;
            // }

            // if ($crawl == 0 && $this->page->next_page > 1) {
            //     $this->page->update(
            //         [
            //             'crawler_date' => now(),
            //             'next_page' => 1,
            //         ]
            //     );
            // }
        } catch (RequestException $e) {
            report($e);

            if ($e->hasResponse()) {
                if ($e->getResponse()?->getStatusCode() == '404') {
                    $this->page->update(
                        [
                            'crawler_date' => now(),
                            'error' => $e->getMessage(),
                            'next_page' => 1,
                        ]
                    );
                }
            } else {
                $this->page->update(
                    [
                        'crawler_date' => now(),
                        'error' => $e->getMessage(),
                    ]
                );
            }
        } catch (\Throwable $e) {
            report($e);

            $this->page->update(
                [
                    'crawler_date' => now(),
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}
