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

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Juzaweb\CMS\Exceptions\GoogleTranslateException;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerContent;

class TranslateCrawlerContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public function __construct(protected CrawlerContent $content, protected string $target)
    {
    }

    public function handle()
    {
        $crawler = app(CrawlerContract::class);

        DB::beginTransaction();
        try {
            $crawler->translate($this->content, $this->target);

            $this->content->update(['status' => CrawlerContent::STATUS_DONE]);

            DB::commit();
        } catch (GoogleTranslateException $e) {
            DB::rollBack();
            $this->content->update(['status' => CrawlerContent::STATUS_TRANSLATE_ERROR]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
