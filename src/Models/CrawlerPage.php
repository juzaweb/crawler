<?php

namespace Juzaweb\Crawler\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrawlerPage extends Model
{
    protected $table = 'crawler_pages';

    protected $fillable = [
        'url',
        'url_with_page',
        'next_page',
        'element_item',
        'website_id',
        'category_ids',
        'active',
        'crawler_date',
    ];

    public $casts = [
        'category_ids' => 'array'
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(CrawlerWebsite::class, 'id', 'website_id');
    }
}
