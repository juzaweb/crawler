<?php

namespace Juzaweb\Modules\Crawler\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Juzaweb\Modules\Crawler\Enums\CrawlerLogStatus;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;

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
}
