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
 * @method static \Illuminate\Database\Eloquent\Builder|CrawLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawLink query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawLink whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawLink whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawLink whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawLink whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawLink whereUrl($value)
 * @mixin \Eloquent
 * @property int $page_id
 * @property int $channel_id
 * @property int $category_id
 * @method static \Illuminate\Database\Eloquent\Builder|CrawLink whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawLink whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawLink wherePageId($value)
 * @property array|null $category_ids
 * @method static \Illuminate\Database\Eloquent\Builder|CrawLink whereCategoryIds($value)
 */
class CrawLink extends Model
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
}
