<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    MIT
 */

namespace Juzaweb\Crawler\Commands\Crawler;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Juzaweb\Crawler\Jobs\ContentCrawlerJob;
use Juzaweb\Crawler\Models\CrawlerLink;
use Symfony\Component\Console\Input\InputOption;

class AutoContentCrawlerCommand extends Command
{
    protected $name = 'crawler:contents';
    protected $description = 'Craw contents command.';

    public function handle(): void
    {
        if (!get_config('crawler_enable')) {
            return;
        }

        $limit = $this->option('limit');
        $sleep = $this->option('sleep');
        $queue = config('crawler.queue.crawler');

        $query = CrawlerLink::with(
            [
                'website',
                'page'
            ]
        )
            ->where('status', '=', CrawlerLink::STATUS_PENDING)
            ->whereHas('website', fn ($q) => $q->where(['active' => 1]))
            ->whereHas(
                'page',
                function ($q) {
                    $q->where(['active' => 1]);
                    if ($this->option('is_resource')) {
                        $q->where(['is_resource_page' => 1]);
                    }
                }
            )
            ->orderBy('id', 'ASC');

        $links = $query->limit($limit)->get();

        $job = 1;
        foreach ($links as $link) {
            $link->update(['status' => CrawlerLink::STATUS_PROCESSING]);

            ContentCrawlerJob::dispatch($link)
                ->onQueue($queue)
                ->delay(Carbon::now()->addSeconds($job * 20));

            $type = $link->page->is_resource_page ? 'Resource' : 'Post';

            $this->info("Creating {$type} from link {$link->url}");

            $job++;
            sleep($sleep);
        }
    }

    protected function getOptions(): array
    {
        return [
            ['limit', null, InputOption::VALUE_OPTIONAL, 'The limit rows crawl per run.', 50],
            ['sleep', null, InputOption::VALUE_OPTIONAL, 'Sleep seconds per crawl.', 1],
            ['is_resource', null, InputOption::VALUE_OPTIONAL, 'Sleep seconds per crawl.', false],
        ];
    }
}
