<?php

namespace Juzaweb\Modules\Crawler\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
        //
    }
}
