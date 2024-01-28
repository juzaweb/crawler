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
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerContent;
use Juzaweb\Crawler\Models\CrawlerLink;
use Juzaweb\Crawler\Models\CrawlerWebsite;
use Juzaweb\CrawlerTranslate\Jobs\TranslateContentJob;
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
        $queue = config('crawler.queue.crawler');
        $translateQueue = config('crawler.queue.translate');
        $translateQueueHigh = config('crawler.queue.translate_high') ?? $translateQueue;
        $transQueue = $this->link->website->queue == CrawlerWebsite::QUEUE_HIGH ? $translateQueueHigh : $translateQueue;
        $targets = get_config('crawler_translate_languages', []);

        try {
            $content = DB::transaction(
                function () {
                    $proxy = null;
                    if ((bool) get_config('crawler_enable_proxy', 0)) {
                        $proxy = app(ProxyManager::class)->random()?->toGuzzleHttpProxy();
                    }

                    $content = app(CrawlerContract::class)->crawContentLink($this->link, $proxy);

                    $this->link->update(['status' => CrawlerLink::STATUS_DONE]);

                    $content->update(['status' => CrawlerContent::STATUS_TRANSLATING]);

                    return $content;
                }
            );

            $skipSource = (bool) get_config('crawler_skip_origin_content', 0);

            if (!$skipSource) {
                (new PostContentJob($this->link, $content->lang))->onQueue($queue);
            }

            // Translate content
            // $targets = collect($targets)->filter(
            //     function ($item) use ($content) {
            //         return $item !== $content->lang;
            //     }
            // )->values()->toArray();
            //
            // foreach ($targets as $target) {
            //     Bus::chain(
            //         [
            //             (new TranslateContentJob($this->link, $target))->onQueue($transQueue),
            //             (new PostContentJob($this->link, $target))->onQueue($queue),
            //         ]
            //     )->dispatch();
            // }
        } catch (\Throwable $e) {
            $this->link->update(['status' => CrawlerLink::STATUS_ERROR]);
            throw $e;
        }
    }
}
