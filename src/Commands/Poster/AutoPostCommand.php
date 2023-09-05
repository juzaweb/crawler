<?php

namespace Juzaweb\Crawler\Commands\Poster;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Juzaweb\Backend\Models\Post;
use Juzaweb\Backend\Models\Taxonomy;
use Juzaweb\Crawler\Jobs\PostContentsJob;
use Juzaweb\Crawler\Models\CrawlerContent;
use Symfony\Component\Console\Input\InputOption;

class AutoPostCommand extends Command
{
    protected $name = 'crawler:posts';
    protected $description = 'Command post crawler content.';

    public function handle(): void
    {
        $max = (int) $this->option('limit');
        $maxId = $this->getContentPostBuilder()->skip($max)->take(1)->value('id');

        Post::setFlushCacheOnUpdate(false);
        Taxonomy::setFlushCacheOnUpdate(false);

        $queue = config('crawler.queue.crawler');

        $job = 0;
        $this->getContentPostBuilder()
            ->select(['id'])
            ->when($maxId, fn ($q) => $q->where('id', '<', $maxId))
            ->chunkById(
                50,
                function ($rows) use (&$job, $queue) {
                    DB::beginTransaction();
                    try {
                        $contentIds = $rows->pluck('id')->toArray();

                        PostContentsJob::dispatch($contentIds)
                            ->onQueue($queue)
                            ->delay(Carbon::now()->addSeconds($job * 110));

                        CrawlerContent::whereIn('id', $contentIds)
                            ->update(['status' => CrawlerContent::STATUS_POSTTING]);

                        DB::commit();
                    } catch (\Throwable $e) {
                        DB::rollBack();
                        throw $e;
                    }

                    $this->info("Posting ". count($contentIds) ." contents...");

                    /*foreach ($rows as $content) {
                        $content->update(['status' => CrawlerContent::STATUS_POSTTING]);

                        DB::beginTransaction();
                        try {
                            $crawler->savePost($content);

                            DB::commit();
                        } catch (\Throwable $e) {
                            DB::rollBack();
                            report($e);
                            $content->update(
                                [
                                    'status' => CrawlerContent::STATUS_ERROR,
                                ]
                            );
                        }

                        $this->info("Post content id ". $content->id);
                        sleep(1);
                    }*/
                    $job ++;
                }
            );
    }

    protected function getContentPostBuilder()
    {
        $skipSource = (bool) get_config('crawler_skip_origin_content', 0);

        return CrawlerContent::with(['link.page', 'link.website'])
            ->where(['status' => CrawlerContent::STATUS_PENDING])
            ->when($skipSource, fn ($q) => $q->where(['is_source' => false]))
            ->whereNull('post_id')
            ->whereNull('resource_id')
            ->orderBy('id', 'ASC');
    }

    protected function getOptions(): array
    {
        return [
            ['limit', null, InputOption::VALUE_OPTIONAL, 'The limit rows crawl per run.', 1000],
        ];
    }
}
