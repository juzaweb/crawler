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
use Juzaweb\Crawler\Models\CrawlerContent;

class TranslateCrawlerContentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public function __construct(protected array $contentIds, protected string $target)
    {
    }

    public function handle(): void
    {
        $translateQueue = config('crawler.queue.translate');

        DB::beginTransaction();
        try {
            $contents = CrawlerContent::with(['link.website'])
                ->where(['status' => CrawlerContent::STATUS_TRANSLATING, 'is_source' => true])
                ->whereIn('id', $this->contentIds)
                ->get();

            foreach ($contents as $content) {
                try {
                    TranslateCrawlerContentJob::dispatch($content, $this->target)->onQueue($translateQueue);
                } catch (\Exception $e) {
                    report($e);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
