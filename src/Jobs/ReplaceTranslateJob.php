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
use Juzaweb\Backend\Models\Post;
use Juzaweb\Crawler\Models\CrawlerContent;
use Juzaweb\Crawler\Models\CrawlerWebsite;

class ReplaceTranslateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public function __construct(protected CrawlerWebsite $website)
    {
    }

    public function handle(): void
    {
        $replaces = $this->website?->translate_replaces;
        if (empty($replaces)) {
            return;
        }

        $searchs = collect($replaces)->pluck('search')->map(fn($item) => "/". preg_quote($item, '/') ."/ui")->toArray();
        $replaces = collect($replaces)
            ->mapWithKeys(fn($item) => [getReplaceSearchKey($item['search']) => $item['replace']])
            ->toArray();

        if (empty($searchs) || empty($replaces)) {
            return;
        }

        $query = Post::with(['crawlerContent.website'])
            ->whereHas(
                'crawlerContent',
                function ($q) {
                    $q->where('status', CrawlerContent::STATUS_DONE);
                    $q->where('website_id', $this->website->id);
                }
            );

        $query->chunk(
            2000,
            function ($posts) use ($searchs, $replaces) {
                foreach ($posts as $post) {
                    $post->title = replaceTranslate($searchs, $replaces, $post->title, $count1);
                    $post->content = replaceTranslate($searchs, $replaces, $post->content, $count2);

                    if ($count1 > 0 || $count2 > 0) {
                        $post->save();
                    }
                }
            }
        );
    }
}
