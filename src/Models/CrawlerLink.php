<?php

namespace Juzaweb\Crawler\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Juzaweb\CMS\Models\Model;
use Kirschbaum\PowerJoins\PowerJoins;

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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read CrawlerPage $page
 * @property-read CrawlerWebsite $website
 * @method static Builder|CrawlerLink newModelQuery()
 * @method static Builder|CrawlerLink newQuery()
 * @method static Builder|CrawlerLink query()
 * @method static Builder|CrawlerLink whereActive($value)
 * @method static Builder|CrawlerLink whereCrawed($value)
 * @method static Builder|CrawlerLink whereCreatedAt($value)
 * @method static Builder|CrawlerLink whereError($value)
 * @method static Builder|CrawlerLink whereId($value)
 * @method static Builder|CrawlerLink wherePageId($value)
 * @method static Builder|CrawlerLink whereStatus($value)
 * @method static Builder|CrawlerLink whereUpdatedAt($value)
 * @method static Builder|CrawlerLink whereUrl($value)
 * @method static Builder|CrawlerLink whereUrlHash($value)
 * @method static Builder|CrawlerLink whereWebsiteId($value)
 * @mixin Eloquent
 */
class CrawlerLink extends Model
{
    use PowerJoins;

    public const STATUS_PENDING = 'pending';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_DONE = 'done';
    public const STATUS_ERROR = 'error';
    public const STATUS_PROCESSING = 'processing';

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

    public function contents(): HasMany
    {
        return $this->hasMany(CrawlerContent::class, 'link_id', 'id');
    }
}
