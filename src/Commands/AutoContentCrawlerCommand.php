<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    MIT
 */

namespace Juzaweb\Crawler\Commands;

use Illuminate\Console\Command;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerLink;
use Symfony\Component\Console\Input\InputOption;

class AutoContentCrawlerCommand extends Command
{
    protected $name = 'crawler:contents';

    protected $description = 'Craw contents command.';

    public function handle()
    {
        $limit = $this->option('limit');
        $sleep = $this->option('sleep');

        $query = CrawlerLink::with(
            [
                'website',
                'page'
            ]
        )
            ->where('status', '=', CrawlerLink::STATUS_PENDING)
            ->whereHas(
                'website',
                function ($q) {
                    $q->where(['active' => 1]);
                }
            )
            ->whereHas(
                'page',
                function ($q) {
                    $q->where(['active' => 1]);
                    //$q->where(['is_resource_page' => 1]);
                }
            )
            ->orderBy('id', 'ASC');

        $links = $query->limit($limit)->get();

        foreach ($links as $link) {
            app(CrawlerContract::class)->crawContentLink($link);

            $type = $link->page->is_resource_page ? 'Resource' : 'Post';

            $link->update(['status' => CrawlerLink::STATUS_DONE]);

            $this->info("Created {$type} from link {$link->url}");

            sleep($sleep);
        }
    }

    protected function getOptions()
    {
        return [
            ['limit', null, InputOption::VALUE_OPTIONAL, 'The limit rows crawl per run.', 5],
            ['sleep', null, InputOption::VALUE_OPTIONAL, 'Sleep seconds per crawl.', 2],
        ];
    }
}
