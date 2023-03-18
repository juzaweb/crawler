<?php

namespace Juzaweb\Crawler\Commands;

use Illuminate\Console\Command;
use Juzaweb\Crawler\Jobs\TranslateCrawlerContent;
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
        $targets = ['vi'];

        $contents = CrawlerContent::with(['link.website'])
            ->where(['status' => CrawlerContent::STATUS_PENDING_TRANSLATE])
            ->limit(20)
            ->get();

        foreach ($contents as $content) {
            foreach ($targets as $target) {
                try {
                    TranslateCrawlerContent::dispatch($content, $target)->onQueue('slow');
                } catch (\Exception $e) {
                    report($e);
                }

                sleep(1);
            }

            $content->update(['status' => CrawlerContent::STATUS_TRANSLATING]);

            sleep(3);
        }
    }
}
