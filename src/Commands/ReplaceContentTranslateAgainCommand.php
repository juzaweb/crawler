<?php

namespace Juzaweb\Crawler\Commands;

use Illuminate\Console\Command;
use Juzaweb\CMS\Traits\CommandData;
use Juzaweb\Crawler\Models\CrawlerContent;
use Symfony\Component\Console\Input\InputOption;

class ReplaceContentTranslateAgainCommand extends Command
{
    use CommandData;

    protected $name = 'crawler:translate-content-replace';

    protected $description = 'Replace again Translate.';

    public function handle()
    {
        $website = $this->option('website');
        $maxId = $this->option('max_id') ?? $this->getCommandData('max_id', 0);

        $query = CrawlerContent::with(['link.website'])
            ->where(['status' => CrawlerContent::STATUS_PENDING, 'is_source' => false])
            ->whereNull('post_id')
            ->whereNull('resource_id')
            ->where('id', '>', $maxId);

        if ($website) {
            $query->where('website_id', $website);
        }

        $contents = $query->orderBy('id', 'ASC')->get();

        foreach ($contents as $content) {
            $replaces = $content->website?->translate_replaces ?? [];
            $searchs = collect($replaces)->pluck('search')->map(fn($item) => "/{$item}/ui")->toArray();
            $replaces = collect($replaces)
                ->mapWithKeys(fn($item) => [getReplaceSearchKey($item['search']) => $item['replace']])
                ->toArray();

            if (empty($searchs) || empty($replaces)) {
                continue;
            }

            $this->info("Replacing content {$content->id}...");

            $count = 0;
            $components = $content->components;
            foreach ($content->components as $key => $component) {
                if (!in_array($key, ['title', 'content'])) {
                    continue;
                }

                $components[$key] = replaceTranslate($searchs, $replaces, $component, $count1);
                $count += $count1;
            }

            if ($count > 0) {
                $content->components = $components;

                $content->save();

                $this->info("Replaced {$count} text");
            }
        }

        if ($contents->isNotEmpty()) {
            $this->setCommandData('max_id', $contents->last()->id);
        }
    }

    protected function getOptions(): array
    {
        return [
            ['website', null, InputOption::VALUE_OPTIONAL, 'Website id for replace.', null],
            ['max_id', null, InputOption::VALUE_OPTIONAL, 'Max id posts for replace.', null],
            ['limit', null, InputOption::VALUE_OPTIONAL, 'The limit rows crawl per run.', 10],
        ];
    }
}
