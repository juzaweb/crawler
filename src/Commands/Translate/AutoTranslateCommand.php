<?php

namespace Juzaweb\Crawler\Commands\Translate;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Juzaweb\CMS\Models\Job;
use Juzaweb\Crawler\Jobs\Bus\PostContentJob;
use Juzaweb\Crawler\Jobs\Bus\TranslateContentJob;
use Juzaweb\Crawler\Models\CrawlerContent;
use Juzaweb\Crawler\Models\CrawlerWebsite;
use Symfony\Component\Console\Input\InputOption;

class AutoTranslateCommand extends Command
{
    protected $name = 'crawler:translate';

    protected $description = 'Translate contents command.';

    public function handle(): void
    {
        if (!get_config('crawler_enable')) {
            return;
        }

        if (!((bool) get_config('crawler_enable_translate', 0))) {
            $this->error('Translate is not enable.');
            return;
        }

        if (!get_config('crawler_enable_proxy') && $this->isGoogleTranslateLock()) {
            $this->error(
                'Translate locked: Google detected unusual traffic from your computer network,'
                .' try again later (2 - 48 hours)'
            );

            return;
        }

        // Check trans jobs in pending
        $jobTranslatings = Job::whereIn(
            'queue',
            [
                config('crawler.queue.translate'),
                config('crawler.queue.translate_high'),
            ]
        )
            ->where('payload', 'like', '%TranslateContentJob%')
            ->count();

        if ($jobTranslatings > 10000) {
            $this->warn("Translate jobs in pending: {$jobTranslatings}");
            return;
        }

        $skipSource = (bool) get_config('crawler_skip_origin_content', 0);
        if ($skipSource) {
            CrawlerContent::with(['link.page', 'link.website'])
                ->where(['status' => CrawlerContent::STATUS_PENDING, 'is_source' => true])
                ->whereNull('post_id')
                ->whereNull('resource_id')
                ->update(['status' => CrawlerContent::STATUS_DONE]);
        }

        $targets = get_config('crawler_translate_languages', []);
        $limit = (int) $this->option('limit');
        $queue = config('crawler.queue.crawler');
        $translateQueue = config('crawler.queue.translate');
        $translateQueueHigh = config('crawler.queue.translate_high') ?? $translateQueue;
        $contentId = $this->option('contentId');

        foreach ($targets as $index => $target) {
            $fnTranslation = function () use (
                $target,
                $queue,
                $limit,
                $contentId,
                $translateQueue,
                $translateQueueHigh
            ) {
                $contents = CrawlerContent::with(['link.website'])
                    ->where(['status' => CrawlerContent::STATUS_DONE, 'is_source' => true])
                    ->whereDoesntHave('children', fn ($q) => $q->where('lang', $target))
                    ->whereHas('page', fn ($q) => $q->where(['active' => 1]))
                    ->when($contentId, fn ($q) => $q->where('id', $contentId))
                    ->orderBy('id', 'ASC')
                    ->limit($limit)
                    ->lockForUpdate()
                    ->get();

                CrawlerContent::whereIn('id', $contents->pluck('id'))
                    ->update(['status' => CrawlerContent::STATUS_TRANSLATING]);

                foreach ($contents as $content) {
                    $transQueue = $content->link->website->queue == CrawlerWebsite::QUEUE_HIGH
                        ? $translateQueueHigh
                        : $translateQueue;

                    Bus::chain(
                        [
                            (new TranslateContentJob($content->link, $target))->onQueue($transQueue),
                            (new PostContentJob($content->link, $target))->onQueue($queue),
                        ]
                    )->dispatch();
                }

                $this->info("Translating ". count($contents) ." posts to {$target}");
            };

            DB::transaction($fnTranslation);

            if (isset($targets[$index + 1])) {
                sleep(2);
            }
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

    protected function getOptions(): array
    {
        return [
            ['limit', null, InputOption::VALUE_OPTIONAL, 'The limit rows crawl per run.', 100],
            ['contentId', null, InputOption::VALUE_OPTIONAL, 'The content id.', null],
        ];
    }
}
