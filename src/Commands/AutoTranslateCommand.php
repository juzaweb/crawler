<?php

namespace Juzaweb\Crawler\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Juzaweb\Crawler\Jobs\TranslateCrawlerContentsJob;
use Juzaweb\Crawler\Models\CrawlerContent;
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
        $crawlerQueue = config('crawler.queue.crawler');

        foreach ($targets as $index => $target) {
            $fnTranslation = function () use ($target, $crawlerQueue, $limit) {
                $contents = CrawlerContent::with(['link.website'])
                    ->where(['status' => CrawlerContent::STATUS_DONE, 'is_source' => true])
                    ->whereDoesntHave('children', fn ($q) => $q->where('lang', $target))
                    ->whereHas('page', fn ($q) => $q->where(['active' => 1]))
                    ->orderBy('id', 'ASC')
                    ->limit($limit)
                    ->lockForUpdate()
                    ->get(['id'])
                    ->pluck('id')
                    ->toArray();

                if (empty($contents)) {
                    return;
                }

                CrawlerContent::whereIn('id', $contents)
                    ->update(['status' => CrawlerContent::STATUS_TRANSLATING]);

                TranslateCrawlerContentsJob::dispatch($contents, $target)->onQueue($crawlerQueue);

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
        ];
    }
}
