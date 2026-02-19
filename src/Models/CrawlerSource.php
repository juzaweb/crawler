<?php

namespace Juzaweb\Modules\Crawler\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Juzaweb\Modules\Core\Models\Model;
use Juzaweb\Modules\Core\Traits\HasAPI;

class CrawlerSource extends Model
{
    use HasAPI, HasUuids;

    protected $table = 'crawler_sources';

    protected $fillable = [
        'name',
        'active',
        'data_type',
        'link_element',
        'link_regex',
        'components',
        'removes',
    ];

    protected $casts = [
        'active' => 'boolean',
        'components' => 'array',
        'removes' => 'array',
    ];

    public function getDataType(): ?\Juzaweb\Modules\Crawler\Contracts\CrawlerDataType
    {
        return app(\Juzaweb\Modules\Crawler\CrawlerRepository::class)->getDataType($this->data_type);
    }
}
