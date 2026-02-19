<?php

namespace Juzaweb\Modules\Crawler;

use Juzaweb\Modules\Crawler\Contracts\CrawlerDataType;

class CrawlerRepository
{
    protected array $dataTypes = [];

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
}
