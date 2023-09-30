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
use Illuminate\Support\Facades\Bus;
use Juzaweb\Crawler\Jobs\Bus\ContentCrawlerJob;
use Juzaweb\Crawler\Jobs\Bus\PostContentJob;
use Juzaweb\Crawler\Jobs\Bus\TranslateContentJob;
use Juzaweb\Crawler\Models\CrawlerContent;
use Juzaweb\Crawler\Models\CrawlerLink;
use Symfony\Component\Console\Input\InputOption;

class AutoContentCrawlerWithBusCommand extends Command
{
    protected $name = 'crawler:bus-contents';

    protected $description = 'Craw contents with bus command.';

    public function handle(): void
    {
        if (!get_config('crawler_enable')) {
            return;
        }

        $limit = $this->option('limit');
        $queue = config('crawler.queue.crawler');
        $translateQueue = config('crawler.queue.translate');

        $skipSource = (bool) get_config('crawler_skip_origin_content', 0);
        if ($skipSource) {
            CrawlerContent::with([])
                ->where(['status' => CrawlerContent::STATUS_PENDING, 'is_source' => true])
                ->whereNull('post_id')
                ->whereNull('resource_id')
                ->update(['status' => CrawlerContent::STATUS_DONE]);
        }

        $query = CrawlerLink::with(
            [
                'website',
                'page',
            ]
        )
            ->joinRelationship('website')
            ->joinRelationship('page')
            ->where('crawler_links.status', '=', CrawlerLink::STATUS_PENDING)
            ->where(['crawler_websites.active' => true, 'crawler_pages.active' => true])
            ->when($this->option('is_resource'), fn($q) => $q->where(['crawler_pages.is_resource_page' => 1]))
            ->orderBy('crawler_links.id', 'ASC');

        $links = $query->limit($limit)->get();

        if ($links->isEmpty()) {
            $this->info('No links found.');
            return;
        }

        $targets = get_config('crawler_translate_languages', []);
        foreach ($links as $link) {
            $link->update(['status' => CrawlerLink::STATUS_PROCESSING]);

            Bus::chain(
                [
                    (new ContentCrawlerJob($link))->onQueue($queue),
                    (new TranslateContentJob($link, $targets[0]))->onQueue($translateQueue),
                    (new PostContentJob($link, $targets[0]))->onQueue($queue),
                ]
            )->dispatch();

            $type = $link->page->is_resource_page ? 'Resource' : 'Post';

            $this->info("Creating {$type} from link {$link->url}");
        }
    }

    protected function getOptions(): array
    {
        return [
            ['limit', null, InputOption::VALUE_OPTIONAL, 'The limit rows crawl per run.', 50],
            ['is_resource', null, InputOption::VALUE_OPTIONAL, 'Sleep seconds per crawl.', false],
        ];
    }
}
