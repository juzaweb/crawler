<?php

namespace Juzaweb\Crawler\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Juzaweb\CMS\Traits\CommandData;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerPage;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FindLinkCommand extends Command
{
    use CommandData;

    protected $name = 'crawler:find';

    protected $description = 'Find link for page by url.';

    public function handle(): void
    {
        $page = CrawlerPage::find($this->argument('page'));

        if (empty($page)) {
            $this->error("Page not found.");
            return;
        }

        $this->info("Find and add links to page: {$page->url}");

        $url = $this->ask("url=", $this->getCommandData('url'));

        $search = $this->ask("search=", $this->getCommandData('search'));

        $this->setCommandData('url', $url);

        $this->setCommandData('search', $search);

        $contents = $this->getClient()->get($url)->getBody()->getContents();

        $html = str_get_html($contents);

        $links = [];
        foreach ($html->find('a') as $e) {
            $href = trim(get_full_url($e->href, $url));
            if (preg_match("/{$search}/i", $href)) {
                $links[] = $href;
            }
        }

        dump($links);

        if ($this->option('save')) {
            app(CrawlerContract::class)->checkAndInsertLinks($links, $page);

            $this->info("Inserted ". count($links) . " links.");
        }
    }

    protected function getClient(): Client
    {
        return new Client(['timeout' => 10]);
    }

    protected function getOptions(): array
    {
        return [
            ['save', null, InputOption::VALUE_NEGATABLE, 'Save links to database.', null],
        ];
    }

    protected function getArguments(): array
    {
        return [
            [
                'page',
                InputArgument::REQUIRED,
                'The page id to add link results.',
                null,
            ],
        ];
    }
}
