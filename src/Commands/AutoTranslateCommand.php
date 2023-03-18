<?php

namespace Juzaweb\Crawler\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Juzaweb\Crawler\Jobs\TranslateCrawlerContentJob;
use Juzaweb\Crawler\Models\CrawlerContent;

class AutoTranslateCommand extends Command
{
    protected $name = 'crawler:translate';

    protected int $jonDepaySecond = 60;

    protected $description = 'Translate contents command.';

    public function handle()
    {
        if ($this->isGoogleTranslateLock()) {
            $this->error(
                'Translate locked: Google detected unusual traffic from your computer network,'
                .' try again later (2 - 48 hours)'
            );

            return;
        }

        $targets = ['vi'];
        $job = 1;
        foreach ($targets as $target) {
            $contents = CrawlerContent::with(['link.website'])
                ->where(['status' => CrawlerContent::STATUS_DONE, 'is_source' => true])
                ->whereDoesntHave('children', fn($q) => $q->where('lang', $target))
                ->orderBy('id', 'ASC')
                ->limit(20)
                ->get();

            foreach ($contents as $content) {
                try {
                    TranslateCrawlerContentJob::dispatch($content, $target)
                        ->delay(Carbon::now()->addSeconds($job * $this->jonDepaySecond));
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

    protected function isGoogleTranslateLock(): bool
    {
        if ($lock = $this->getStorageDisk()->get('lock-translate.txt')) {
            if ($lock < now()->subHours(2)->format('Y-m-d H:i:s')) {
                return true;
            }

            $this->getStorageDisk()->delete('lock-translate.txt');
        }

        return false;
    }

    protected function getStorageDisk(): Filesystem
    {
        return Storage::disk('local');
    }
}
