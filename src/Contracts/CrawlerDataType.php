<?php

namespace Juzaweb\Modules\Crawler\Contracts;

use Illuminate\Database\Eloquent\Model;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;

interface CrawlerDataType
{
    public function save(CrawlerLog $crawlerLog): Model;

    public function components(): array;

    public function rules(): array;

    public function getModel(): string;

    public function getLabel(): string;

    public function getCategoryClass(): ?string;
}
