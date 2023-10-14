<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/juzacms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Jobs\Bus;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerContent;
use Juzaweb\Crawler\Models\CrawlerLink;
use Juzaweb\Proxies\Contracts\ProxyManager;

class ContentCrawlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;

    public function __construct(protected CrawlerLink $link)
    {
    }

    public function handle(): void
    {
        try {
            DB::transaction(
                function () {
                    $proxy = null;
                    if ((bool) get_config('crawler_enable_proxy', 0)) {
                        $proxy = app(ProxyManager::class)->random()?->toGuzzleHttpProxy();
                    }

                    $content = app(CrawlerContract::class)->crawContentLink($this->link, $proxy);

                    $this->link->update(['status' => CrawlerLink::STATUS_DONE]);

                    $content->update(['status' => CrawlerContent::STATUS_TRANSLATING]);
                }
            );
        } catch (\Throwable $e) {
            $this->link->update(['status' => CrawlerLink::STATUS_ERROR]);
            throw $e;
        }
    }
}
