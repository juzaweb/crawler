<?php

namespace Juzaweb\Crawler\Commands;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Jobs\CrawlerLinksJob;
use Juzaweb\Crawler\Models\CrawlerPage;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CrawlerLinkManualCommand extends Command
{
    protected $name = 'crawler:manual-links';

    protected $description = 'Crawl links manual command.';

    public function handle(): void
    {
        $page = CrawlerPage::find($this->argument('page'));

        throw_unless($page, new \Exception('Page not exists.'));

        $i = 1;
        while ($i <= $this->option('limit')) {
            $this->info("Craw {$page->url} in process...");
            CrawlerLinksJob::dispatchSync($page);
            $this->info("Crawed successful link");
            sleep(1);
            $i++;
        }
    }

    protected function getOptions(): array
    {
        return [
            ['limit', null, InputOption::VALUE_REQUIRED, 'Limit craw page.', 10],
        ];
    }

    protected function getArguments(): array
    {
        return [
            [
                'page',
                InputArgument::REQUIRED,
                'The page id crawl.',
                null,
            ],
        ];
    }
}
