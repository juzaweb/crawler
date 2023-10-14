<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/juzacms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Juzaweb\Backend\Http\Controllers\Backend\PageController;
use Juzaweb\Backend\Models\Post;
use Juzaweb\CMS\Models\Job;
use Juzaweb\Crawler\Models\CrawlerContent;
use Juzaweb\Crawler\Models\CrawlerLink;

class StatsController extends PageController
{
    public function index(): Factory|View
    {
        $title = trans('crawler::content.stats');

        $linkDone = CrawlerLink::where(['status' => CrawlerLink::STATUS_DONE])->count();
        $linkError = CrawlerLink::where(['status' => CrawlerLink::STATUS_ERROR])->count();
        $linkPending = CrawlerLink::where(['status' => CrawlerLink::STATUS_PENDING])->count();

        $contentPending = CrawlerContent::where(['status' => CrawlerContent::STATUS_PENDING])->count();
        $contentError = CrawlerContent::where(['status' => CrawlerContent::STATUS_ERROR])->count();
        $contentDone = CrawlerContent::where(['status' => CrawlerContent::STATUS_DONE])->count();

        $jobs = Job::count();
        $jobCrawlers = Job::where(['queue' => config('crawler.queue.crawler')])->count();
        $jobContents = Job::where(['queue' => config('crawler.queue.crawler')])
            ->where('payload', 'like', '%ContentCrawlerJob%')
            ->count();
        $jobTranslatings = Job::whereIn(
            'queue',
            [
                config('crawler.queue.translate'),
                config('crawler.queue.translate_high'),
            ]
        )
            ->where('payload', 'like', '%TranslateContentJob%')
            ->count();

        $diskFree = Cache::store('file')->remember(
            'crawler_free_disk',
            3600,
            fn () => format_size_units(disk_free_space('/')),
        );

        return view(
            'crawler::stats.index',
            compact(
                'title',
                'linkDone',
                'linkError',
                'linkPending',
                'contentPending',
                'contentError',
                'contentDone',
                'jobs',
                'jobContents',
                'jobTranslatings',
                'jobCrawlers',
                'diskFree'
            )
        );
    }

    public function crawlerChart(): JsonResponse
    {
        $result = Cache::store('file')->remember(
            cache_prefix('crawler_chart'),
            3600,
            function () {
                $result = [];
                $today = Carbon::today();
                $minDay = $today->subDays(7);

                for ($i = 1; $i <= 7; $i++) {
                    $day = $minDay->addDay();
                    $result[] = [
                        $day->format('Y-m-d'),
                        $this->countCrawContentsCrawPerDay($day->format('Y-m-d')),
                        $this->countCrawContentsTranslatePerDay($day->format('Y-m-d')),
                        $this->countPostsPerDay($day->format('Y-m-d')),
                    ];
                }

                return $result;
            }
        );

        return response()->json($result);
    }

    protected function countCrawContentsCrawPerDay(string $day): int
    {
        return CrawlerContent::whereDate('created_at', '=', $day)
            ->where(['is_source' => true])
            ->count('id');
    }

    protected function countCrawContentsTranslatePerDay(string $day): int
    {
        return CrawlerContent::whereDate('created_at', '=', $day)
            ->where(['is_source' => false])
            ->count('id');
    }

    protected function countPostsPerDay(string $day): int
    {
        return Post::whereDate('created_at', '=', $day)
            ->whereExists(
                fn($q) => $q->select(['id'])
                    ->from(CrawlerContent::getTableName())
                    ->whereColumn('post_id', 'posts.id')
            )
            ->count('id');
    }
}
