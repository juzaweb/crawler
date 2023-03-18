<?php

namespace Juzaweb\Crawler\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Juzaweb\Crawler\Jobs\TranslateCrawlerContentJob;
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
        $job = 1;

        foreach ($targets as $target) {
            $contents = CrawlerContent::with(['link.website'])
                ->where(['status' => CrawlerContent::STATUS_DONE, 'is_source' => true])
                ->whereDoesntHave('children', fn($q) => $q->where('lang', $target))
                ->limit(20)
                ->get();

            foreach ($contents as $content) {
                try {
                    TranslateCrawlerContentJob::dispatch($content, $target)
                        ->onQueue('slow');
                    //->delay(Carbon::now()->addSeconds($job * 60));
                } catch (\Exception $e) {
                    report($e);
                }

                $this->info("Translate {$content->id} in process...");

                $job++;
                sleep(1);
            }

            sleep(3);
        }
    }
}
