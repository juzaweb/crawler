<?php

namespace Juzaweb\Modules\Crawler;

use Illuminate\Support\Collection;
use Juzaweb\Modules\Crawler\Contracts\Crawler;
use Juzaweb\Modules\Crawler\Contracts\CrawlerDataType;

class CrawlerRepository implements Crawler
{
    protected array $dataTypes = [];

    public function crawl(Collection $pages): PoolCrawler
    {
        return (new PoolCrawler($pages))->crawl();
    }

    public function registerDataType(string $key, callable $callback): void
    {
        $this->dataTypes[$key] = $callback;
    }

    public function getDataType(string $key): ?CrawlerDataType
    {
        $callback = $this->dataTypes[$key] ?? null;

        if (!$callback) {
            return null;
        }

        return $callback();
    }

    public function getDataTypes(): array
    {
        return collect($this->dataTypes)
            ->map(
                function ($callback) {
                    /** @var CrawlerDataType $dataType */
                    $dataType = $callback();

                    return $dataType->getLabel();
                }
            )
            ->toArray();
    }
}
