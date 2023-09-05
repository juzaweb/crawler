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

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Juzaweb\Crawler\Contracts\CrawlerContract;
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
        DB::beginTransaction();
        try {
            $proxy = null;
            if ((bool) get_config('crawler_enable_proxy', 0)) {
                $proxy = app(ProxyManager::class)->random()?->toGuzzleHttpProxy();
            }

            app(CrawlerContract::class)->crawContentLink($this->link, $proxy);

            $this->link->update(['status' => CrawlerLink::STATUS_DONE]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->link->update(['status' => CrawlerLink::STATUS_ERROR]);
            throw $e;
        }
    }
}
