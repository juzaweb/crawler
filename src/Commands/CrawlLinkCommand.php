<?php
/**
 * LARABIZ CMS - Full SPA Laravel CMS
 *
 * @package    larabizcms/larabiz
 * @author     The Anh Dang
 * @link       https://larabiz.com
 */

namespace Juzaweb\Modules\Crawler\Commands;

use Illuminate\Console\Command;
use Juzaweb\Modules\Crawler\Enums\CrawlerLogStatus;
use Juzaweb\Modules\Crawler\Jobs\CrawlLinkJob;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;
use Symfony\Component\Console\Input\InputOption;

class CrawlLinkCommand extends Command
{
    protected $name = 'crawl:links';

    protected $description = 'Crawl links.';

    public function handle(): int
    {
        $limit = $this->option('limit');

        $links = CrawlerLog::with([
            'source',
            'page',
        ])
            ->join('crawler_sources', 'crawler_sources.id', '=', 'crawler_logs.source_id')
            ->join('crawler_pages', 'crawler_pages.id', '=', 'crawler_logs.page_id')
            ->select('crawler_logs.*')
            ->where('crawler_sources.active', true)
            ->where('crawler_pages.active', true)
            ->where(['status' => CrawlerLogStatus::PENDING])
            ->limit($limit)
            ->get();

        foreach ($links as $link) {
            $link->update(['status' => CrawlerLogStatus::PROCESSING]);
            CrawlLinkJob::dispatch($link);
        }

        $this->info("Dispatched " . $links->count() . " jobs.");

        return self::SUCCESS;
    }

    protected function getOptions(): array
    {
        return [
            ['limit', null, InputOption::VALUE_OPTIONAL, 'Limit crawling.', 50],
        ];
    }
}
