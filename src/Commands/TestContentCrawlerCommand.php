<?php

namespace Juzaweb\Crawler\Commands;

use Illuminate\Console\Command;
use Juzaweb\CMS\Traits\CommandData;
use Juzaweb\Crawler\Contracts\CrawlerContract;

class TestContentCrawlerCommand extends Command
{
    use CommandData;

    protected $name = 'crawler:test-contents';

    protected $description = 'Craw content for test from url command.';

    public function handle()
    {
        $url = $this->ask('url=', $this->getCommandData('url'));

        $template = $this->ask('template=', $this->getCommandData('template'));

        $this->setCommandData('url', $url);

        $this->setCommandData('template', $template);

        $results = app(CrawlerContract::class)->crawContentUrl(
            $url,
            app($template)
        );

        dd($results);
    }
}
