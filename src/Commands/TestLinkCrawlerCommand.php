<?php

namespace Juzaweb\Crawler\Commands;

use Illuminate\Console\Command;
use Juzaweb\CMS\Traits\CommandData;
use Juzaweb\Crawler\Contracts\CrawlerContract;

class TestLinkCrawlerCommand extends Command
{
    use CommandData;

    protected $name = 'crawler:test-links';

    protected $description = 'Craw links from url command.';

    public function handle()
    {
        $url = $this->ask('url=', $this->getCommandData('url'));

        $template = $this->ask('template=', $this->getCommandData('template'));

        $this->setCommandData('url', $url);

        $this->setCommandData('template', $template);

        $results = app(CrawlerContract::class)->crawLinksUrl(
            $url,
            app($template)
        );

        dd($results);
    }
}
