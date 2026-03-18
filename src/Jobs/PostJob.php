<?php

namespace Juzaweb\Modules\Crawler\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Juzaweb\Modules\Crawler\Enums\CrawlerLogStatus;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;
use Throwable;

class PostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected CrawlerLog $crawlerLog)
    {
        //
    }

    public function handle(): void
    {
        DB::transaction(
            function () {
                $post = $this->crawlerLog->source->getDataType()?->save($this->crawlerLog);

                $this->crawlerLog->update([
                    'status' => CrawlerLogStatus::COMPLETED,
                    'post_id' => $post->id,
                    'post_type' => $post->getMorphClass(),
                    'error' => null,
                ]);
            }
        );
    }

    public function failed(Throwable $exception): void
    {
        $this->crawlerLog->update([
            'status' => CrawlerLogStatus::FAILED_POSTING,
            'error' => get_error_by_exception($exception),
        ]);
    }
}
