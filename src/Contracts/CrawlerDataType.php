<?php

namespace Juzaweb\Modules\Crawler\Contracts;

use Illuminate\Database\Eloquent\Model;

interface CrawlerDataType
{
    public function save(array $data): Model;

    public function components(): array;

    public function rules(): array;

    public function getModel(): string;

    public function getLabel(): string;
}
