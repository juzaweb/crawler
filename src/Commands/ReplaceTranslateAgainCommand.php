<?php

namespace Juzaweb\Crawler\Commands;

use Illuminate\Console\Command;
use Juzaweb\CMS\Traits\CommandData;
use Juzaweb\Crawler\Jobs\ReplaceTranslateJob;
use Juzaweb\Crawler\Models\CrawlerWebsite;
use Symfony\Component\Console\Input\InputOption;

class ReplaceTranslateAgainCommand extends Command
{
    use CommandData;

    protected $name = 'crawler:translate-replace-again';

    protected $description = 'Replace again Translate.';

    public function handle()
    {
        $websiteId = $this->option('website');

        $query = CrawlerWebsite::where(['active' => true])
            ->when(
                $websiteId,
                fn ($q2) => $q2->where('website_id', $websiteId)
            );

        $websites = $query->get();

        foreach ($websites as $website) {
            ReplaceTranslateJob::dispatch($website);
        }
    }

    protected function getOptions(): array
    {
        return [
            ['website', null, InputOption::VALUE_OPTIONAL, 'Website id for replace.', null],
        ];
    }
}
