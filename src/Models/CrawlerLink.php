<?php

namespace Juzaweb\Crawler\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juzaweb\CMS\Models\Model;

/**
 * Juzaweb\Crawler\Models\CrawLink
 *
 * @property int $id
 * @property string $url
 * @property int $website_id
 * @property int $status
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Juzaweb\Crawler\Models\CrawlerTemplate|null $template
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereUrl($value)
 * @mixin \Eloquent
 * @property int $page_id
 * @property int $channel_id
 * @property int $category_id
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink wherePageId($value)
 * @property array|null $category_ids
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereCategoryIds($value)
 * @property string $url_hash
 * @property int $auto_craw
 * @property int $active
 * @property-read \Juzaweb\Crawler\Models\CrawlerWebsite $website
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereAutoCraw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereUrlHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereWebsiteId($value)
 */
class CrawlerLink extends Model
{
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DONE = 'done';
    const STATUS_ERROR = 'error';
    const STATUS_PROCESSING = 'processing';

    protected $table = 'crawler_links';

    protected $fillable = [
        'url',
        'website_id',
        'category_ids',
        'error',
    ];

    public $casts = [
        'category_ids' => 'array',
        'error' => 'array',
    ];

    public static function getAllStatus(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
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
}
