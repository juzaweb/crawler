<?php

namespace Juzaweb\Crawler\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Juzaweb\CMS\Models\Model;

class CrawlerWebsite extends Model
{
    protected $table = 'crawler_websites';

    protected $fillable = [
        'domain',
        'has_ssl',
        'active',
        'template_id'
    ];

    public function pages(): HasMany
    {
        return $this->hasMany(CrawlerPage::class, 'website_id', 'id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CrawlerTemplate::class, 'template_id', 'id');
    }
}
