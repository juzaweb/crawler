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
use Illuminate\Support\Facades\DB;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerContent;
use Juzaweb\Crawler\Models\CrawlerLink;

class PostContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 30;

    public function __construct(protected CrawlerLink $link, protected string $target)
    {
    }

    public function handle(): void
    {
        $crawler = app(CrawlerContract::class);
        $content = $this->link->contents()->where(['lang' => $this->target])->first();

        try {
            DB::transaction(fn () => $crawler->savePost($content));
        } catch (\Throwable $e) {
            $content->update(
                [
                    'status' => CrawlerContent::STATUS_ERROR,
                ]
            );

            throw $e;
        }
    }
}
