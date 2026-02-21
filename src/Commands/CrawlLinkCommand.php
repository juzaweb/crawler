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
use Illuminate\Support\Facades\DB;
use Juzaweb\Modules\Crawler\Enums\CrawlerLogStatus;
use Juzaweb\Modules\Crawler\Facades\Crawler;
use Juzaweb\Modules\Crawler\Link;
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

        $crawlLinks = collect();
        $links->each(
            function (CrawlerLog $link) use ($crawlLinks) {
                $linkCrawler = new Link(
                    $link->url,
                    $link->source->mapComponentsWithElements($link->url),
                );

                $crawlLinks->push($linkCrawler);
            }
        );

        $results = Crawler::crawl($crawlLinks)->getResult();

        $successIds = DB::transaction(
            function () use ($results, $links) {
                $successIds = [];
                foreach ($results as $key => $result) {
                    $link = $links[$key];
                    if (isset($result['error'])) {
                        $link->update(['status' => CrawlerLogStatus::FAILED, 'error' => $result['error']]);
                        $this->error($result['error']);
                        continue;
                    }

                    $link->update(['status' => CrawlerLogStatus::COMPLETED, 'content_json' => $result]);

                    $this->info("Crawled {$link->url}");
                }

                return $successIds;
            }
        );

        $this->info("Inserted " . count($successIds) . " contents");

        return self::SUCCESS;
    }

    protected function getOptions(): array
    {
        return [
            ['limit', null, InputOption::VALUE_OPTIONAL, 'Limit crawling.', 50],
        ];
    }
}
