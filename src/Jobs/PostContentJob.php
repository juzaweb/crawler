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
use Juzaweb\Crawler\Models\CrawlerContent;

class PostContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 30;

    public function __construct(protected CrawlerContent $content)
    {
    }

    public function handle(): void
    {
        $crawler = app(CrawlerContract::class);

        DB::beginTransaction();
        try {
            $crawler->savePost($this->content);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            $this->content->update(
                [
                    'status' => CrawlerContent::STATUS_ERROR,
                ]
            );
        }
    }
}
