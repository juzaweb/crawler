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
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Juzaweb\Backend\Models\Post;
use Juzaweb\Backend\Models\Taxonomy;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerContent;

class PostContentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(protected array $contentIds)
    {
    }

    public function handle(): void
    {
        $contents = CrawlerContent::with(['link.page', 'link.website'])
            ->where(['status' => CrawlerContent::STATUS_POSTTING])
            ->whereNull('post_id')
            ->whereNull('resource_id')
            ->whereIn('id', $this->contentIds)
            ->get();

        $crawler = app(CrawlerContract::class);
        Post::setFlushCacheOnUpdate(false);
        Taxonomy::setFlushCacheOnUpdate(false);

        foreach ($contents as $content) {
            try {
                DB::transaction(
                    function () use ($content, $crawler) {
                        $crawler->savePost($content);
                    }
                );
            } catch (\Throwable $e) {
                report($e);
                $content->update(
                    [
                        'status' => CrawlerContent::STATUS_ERROR,
                    ]
                );
            }
        }
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping())->releaseAfter(120)];
    }
}
