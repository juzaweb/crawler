<?php

namespace Juzaweb\Crawler\Commands\Tester;

use Illuminate\Console\Command;
use Juzaweb\CMS\Traits\CommandData;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerContent;

class TestTranslateCrawlerCommand extends Command
{
    use CommandData;

    protected $signature = 'crawler:test-translate';

    protected $description = 'Command test translate crawler.';

    public function handle()
    {
        $contentId = $this->ask('content_id=', $this->getCommandData('content_id'));

        $content = CrawlerContent::findOrFail($contentId);

        $target = $this->ask('target=', $this->getCommandData('target', 'vi'));

        $this->setCommandData('content_id', $contentId);

        $this->setCommandData('target', $target);

        $results = app(CrawlerContract::class)->translateCrawlerContent($content, $target);

        var_dump($results);
    }
}
