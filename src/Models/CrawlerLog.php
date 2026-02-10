<?php

namespace Juzaweb\Modules\Crawler\Models;

use Juzaweb\Modules\Core\Models\Model;
use Juzaweb\Modules\Core\Traits\HasAPI;

class CrawlerLog extends Model
{
    use HasAPI;

    protected $table = 'crawler_logs';

    protected $fillable = [
        'url',
        'url_hash',
        'source_id',
        'page_id',
        'status',
        'error',
        'content_json',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'error' => 'array',
        'content_json' => 'array',
    ];

    public function source()
    {
        return $this->belongsTo(CrawlerSource::class, 'source_id');
    }

    public function page()
    {
        return $this->belongsTo(CrawlerPage::class, 'page_id');
    }
}
