<?php

namespace Juzaweb\Crawler\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juzaweb\CMS\Models\Model;

/**
 * Juzaweb\Crawler\Models\CrawlerContent
 *
 * @property int $id
 * @property array $components
 * @property string|null $lang
 * @property int $link_id
 * @property int $page_id
 * @property int|null $post_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Juzaweb\Crawler\Models\CrawlerLink $link
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerContent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerContent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerContent query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerContent whereComponents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerContent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerContent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerContent whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerContent whereLinkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerContent wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerContent wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerContent whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerContent whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CrawlerContent extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_DONE = 'done';
    const STATUS_ERROR = 'error';

    protected $table = 'crawler_contents';
    protected $fillable = [
        'components',
        'lang',
        'link_id',
        'page_id',
        'post_id',
        'status',
    ];

    public $casts = [
        'components' => 'array',
    ];

    public function link(): BelongsTo
    {
        return $this->belongsTo(CrawlerLink::class, 'link_id', 'id');
    }
}
