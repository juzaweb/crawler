<?php

namespace Juzaweb\Modules\Crawler\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Juzaweb\Modules\Core\Models\Model;
use Juzaweb\Modules\Core\Traits\HasAPI;

class CrawlerPage extends Model
{
    use HasAPI, HasUuids;

    protected $table = 'crawler_pages';

    protected $fillable = [
        'source_id',
        'url',
        'url_hash',
        'url_with_page',
        'next_page',
        'active',
        'crawled_at',
        'error',
        'locale',
    ];

    protected $casts = [
        'active' => 'boolean',
        'error' => 'array',
        'crawled_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(
            function ($model) {
                if ($model->isDirty('url')) {
                    $model->url_hash = sha1($model->url);
                }

                if (empty($model->locale)) {
                    $model->locale = app()->getLocale();
                }
            }
        );
    }
}
