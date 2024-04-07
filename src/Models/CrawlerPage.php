<?php

namespace Juzaweb\Crawler\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Traits\ResourceModel;
use Juzaweb\Crawler\Interfaces\CrawlerPageEntity;

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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $url_hash
 * @property string|null $error
 * @property int $is_resource_page
 * @property string $post_type
 * @property int $auto_craw
 * @property-read CrawlerWebsite|null $website
 * @method static Builder|CrawlerPage newModelQuery()
 * @method static Builder|CrawlerPage newQuery()
 * @method static Builder|CrawlerPage query()
 * @method static Builder|CrawlerPage whereActive($value)
 * @method static Builder|CrawlerPage whereCategoryIds($value)
 * @method static Builder|CrawlerPage whereCrawlerDate($value)
 * @method static Builder|CrawlerPage whereCreatedAt($value)
 * @method static Builder|CrawlerPage whereId($value)
 * @method static Builder|CrawlerPage whereListUrl($value)
 * @method static Builder|CrawlerPage whereListUrlPage($value)
 * @method static Builder|CrawlerPage whereNextPage($value)
 * @method static Builder|CrawlerPage whereUpdatedAt($value)
 * @method static Builder|CrawlerPage whereWebsiteId($value)
 * @method static Builder|CrawlerPage whereAutoCraw($value)
 * @method static Builder|CrawlerPage wherePostType($value)
 * @method static Builder|CrawlerPage whereUrl($value)
 * @method static Builder|CrawlerPage whereUrlWithPage($value)
 * @method static Builder|CrawlerPage whereError($value)
 * @method static Builder|CrawlerPage whereIsResourcePage($value)
 * @method static Builder|CrawlerPage whereUrlHash($value)
 * @property int|null $parent_post_id
 * @method static Builder|CrawlerPage whereParentPostId($value)
 * @property string $lang
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Crawler\Models\CrawlerPage whereLang($value)
 * @property int|null $max_page
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Crawler\Models\CrawlerPage whereMaxPage($value)
 * @property int $site_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Crawler\Models\CrawlerPage whereFilter(array $params = [])
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Crawler\Models\CrawlerPage whereSiteId($value)
 * @mixin \Eloquent
 */
class CrawlerPage extends Model implements CrawlerPageEntity
{
    use ResourceModel;

    protected string $fieldName = 'url';

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
        'parent_post_id',
        'is_resource_page',
        'lang',
        'max_page',
    ];

    public $casts = [
        'category_ids' => 'array',
        'is_resource_page' => 'boolean',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(CrawlerWebsite::class, 'website_id', 'id');
    }
}
