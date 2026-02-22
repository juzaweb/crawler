<?php

namespace Juzaweb\Modules\Crawler\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Juzaweb\Modules\Core\Models\Model;
use Juzaweb\Modules\Core\Traits\HasAPI;
use Juzaweb\Modules\Crawler\Enums\CrawlerLogStatus;

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
        'post_type',
        'post_id',
    ];

    protected $casts = [
        'error' => 'array',
        'content_json' => 'array',
        'status' => CrawlerLogStatus::class,
    ];

    public $filterable = [
        'source_id',
        'page_id',
        'status',
        'post_type',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(CrawlerSource::class, 'source_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(CrawlerPage::class, 'page_id');
    }

    public function post(): MorphTo
    {
        return $this->morphTo();
    }
}
