<?php

namespace Juzaweb\Modules\Crawler\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Juzaweb\Modules\Admin\Models\Model;
use Juzaweb\Modules\Admin\Traits\HasAPI;
use Juzaweb\Modules\Admin\Traits\Networkable;

class CrawlerSource extends Model
{
    use HasAPI, HasUuids, Networkable;

    protected $table = 'crawler_sources';

    protected $fillable = [
        'domain',
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
}
