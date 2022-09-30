<?php

namespace Juzaweb\Crawler\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Juzaweb\Crawler\Models\CrawlerPage
 *
 * @property int $id
 * @property string $url
 * @property string|null $url_with_page
 * @property int $website_id
 * @property array|null $category_ids
 * @property int $next_page
 * @property int $active
 * @property string $crawler_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Juzaweb\Crawler\Models\CrawlerWebsite|null $website
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerPage query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerPage whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerPage whereCategoryIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerPage whereCrawlerDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerPage whereListUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerPage whereListUrlPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerPage whereNextPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerPage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerPage whereWebsiteId($value)
 * @mixin \Eloquent
 * @property string $post_type
 * @property int $auto_craw
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerPage whereAutoCraw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerPage wherePostType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerPage whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerPage whereUrlWithPage($value)
 */
class CrawlerPage extends Model
{
    protected $table = 'crawler_pages';

    protected $fillable = [
        'url',
        'url_with_page',
        'next_page',
        'post_type',
        'website_id',
        'category_ids',
        'active',
        'crawler_date',
        'url_hash',
        'is_resource_page'
    ];

    public $casts = [
        'category_ids' => 'array'
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(CrawlerWebsite::class, 'website_id', 'id');
    }
}
