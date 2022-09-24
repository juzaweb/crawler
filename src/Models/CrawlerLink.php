<?php

namespace Juzaweb\Crawler\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juzaweb\CMS\Models\Model;

class CrawlerLink extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DONE = 'done';
    const STATUS_ERROR = 'error';
    const STATUS_PROCESSING = 'processing';

    protected $table = 'crawler_links';

    protected $fillable = [
        'url',
        'website_id',
        'page_id',
        'error',
    ];

    public static function getAllStatus(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_DONE => 'Done',
            self::STATUS_ERROR => 'Error',
            self::STATUS_PROCESSING => 'Processing',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(CrawlerWebsite::class, 'website_id', 'id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(CrawlerPage::class, 'page_id', 'id');
    }
}
