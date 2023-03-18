<?php

namespace Juzaweb\Crawler\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Juzaweb\Crawler\Models\CrawlerLink $link
 * @method static Builder|CrawlerContent newModelQuery()
 * @method static Builder|CrawlerContent newQuery()
 * @method static Builder|CrawlerContent query()
 * @method static Builder|CrawlerContent whereComponents($value)
 * @method static Builder|CrawlerContent whereCreatedAt($value)
 * @method static Builder|CrawlerContent whereId($value)
 * @method static Builder|CrawlerContent whereLang($value)
 * @method static Builder|CrawlerContent whereLinkId($value)
 * @method static Builder|CrawlerContent wherePageId($value)
 * @method static Builder|CrawlerContent wherePostId($value)
 * @method static Builder|CrawlerContent whereStatus($value)
 * @method static Builder|CrawlerContent whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $resource_id
 * @method static Builder|CrawlerContent whereResourceId($value)
 */
class CrawlerContent extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_PENDING_TRANSLATE = 'pending_translate';
    const STATUS_TRANSLATING = 'translating';
    const STATUS_DONE = 'done';
    const STATUS_ERROR = 'error';
    const STATUS_TRANSLATE_ERROR = 'translate_error';

    protected $table = 'crawler_contents';
    protected $fillable = [
        'components',
        'lang',
        'link_id',
        'page_id',
        'post_id',
        'resource_id',
        'status',
        'locale',
    ];

    public $casts = [
        'components' => 'array',
    ];

    public function link(): BelongsTo
    {
        return $this->belongsTo(CrawlerLink::class, 'link_id', 'id');
    }
}
