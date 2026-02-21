<?php

namespace Juzaweb\Modules\Crawler\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juzaweb\Modules\Core\Models\Model;

class CrawlerTaxonomy extends Model
{
    use HasUuids;

    protected $table = 'crawler_taxonomies';

    protected $fillable = [
        'crawler_page_id',
        'taxonomy_id',
        'taxonomy_type',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(CrawlerPage::class, 'crawler_page_id');
    }
}
