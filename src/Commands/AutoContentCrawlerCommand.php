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

class AutoContentCrawlerCommand extends Command
{
    protected $name = 'crawler:contents';

    protected $description = 'Craw contents command.';

    public function handle()
    {
        $query = CrawlerLink::with(
            [
                'website'
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
                }
            )
            ->inRandomOrder();

        $links = $query->limit(5)->get();

        foreach ($links as $link) {
            app(CrawlerContract::class)->crawContentLink($link);
        }
    }
}
