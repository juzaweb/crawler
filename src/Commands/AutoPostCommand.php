<?php

namespace Juzaweb\Crawler\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerContent;

class AutoPostCommand extends Command
{
    protected $signature = 'crawler:posts';

    protected $description = 'Command post crawler content.';

    public function handle()
    {
        $contents = CrawlerContent::with(['link.website'])
            ->where(['status' => CrawlerContent::STATUS_PENDING])
            ->whereNull('post_id')
            ->whereNull('resource_id')
            ->orderBy('id', 'ASC')
            ->limit(20)
            ->get();

        $crawler = app(CrawlerContract::class);
        foreach ($contents as $content) {
            if ($content->is_source && 1) {
                $content->update(['status' => CrawlerContent::STATUS_DONE]);
                continue;
            }

            DB::beginTransaction();
            try {
                $crawler->savePost($content);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                report($e);
                $content->update(
                    [
                        'status' => CrawlerContent::STATUS_ERROR,
                    ]
                );
            }
        }
    }
}
