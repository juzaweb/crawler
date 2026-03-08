<?php

namespace Juzaweb\Modules\Crawler\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Juzaweb\Modules\Crawler\Enums\CrawlerLogStatus;
use Juzaweb\Modules\Crawler\Facades\Crawler;
use Juzaweb\Modules\Crawler\Link;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;

class CrawlLinkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected CrawlerLog $crawlerLog)
    {
    }

    public function handle(): void
    {
        try {
            $link = new Link(
                $this->crawlerLog->url,
                $this->crawlerLog->source->mapComponentsWithElements($this->crawlerLog->url)
            );

            $result = Crawler::crawl(collect([$link]))->getResult()->first();

            if (isset($result['error'])) {
                $this->crawlerLog->update([
                    'status' => CrawlerLogStatus::FAILED,
                    'error' => ['message' => $result['error']]
                ]);
                return;
            }

            unset($result['error']);

            DB::transaction(function () use ($result) {
                $this->crawlerLog->update([
                    'status' => CrawlerLogStatus::CRAWLED,
                    'content_json' => $result,
                    'error' => null,
                ]);

                PostJob::dispatch($this->crawlerLog);

                $this->crawlerLog->update(['status' => CrawlerLogStatus::POSTING]);
            });

        } catch (\Throwable $e) {
            $this->crawlerLog->update([
                'status' => CrawlerLogStatus::FAILED,
                'error' => ['message' => $e->getMessage()]
            ]);
            report($e);
        }
    }
}
