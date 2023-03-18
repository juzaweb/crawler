<?php

namespace Juzaweb\Crawler\Commands;

use Illuminate\Console\Command;
use Juzaweb\Crawler\Models\CrawlerContent;

class AutoTranslateCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'crawler:translate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate contents command.';

    public function handle()
    {
        CrawlerContent::with(['link.website'])->where(['status' => CrawlerContent::STATUS_PENDING]);
    }
}
