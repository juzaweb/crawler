<?php

namespace Juzaweb\Crawler\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juzaweb\CMS\Models\Model;

/**
 * Juzaweb\Crawler\Models\CrawlerLink
 *
 * @property int $id
 * @property string $url
 * @property string $url_hash
 * @property int $website_id
 * @property int $page_id
 * @property string $status
 * @property mixed|null $error
 * @property int $crawed
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Juzaweb\Crawler\Models\CrawlerPage $page
 * @property-read \Juzaweb\Crawler\Models\CrawlerWebsite $website
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereCrawed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereUrlHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerLink whereWebsiteId($value)
 * @mixin \Eloquent
 */
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
        'status',
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
