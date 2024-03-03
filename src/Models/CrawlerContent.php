<?php

namespace Juzaweb\Crawler\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Juzaweb\Backend\Models\Post;
use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Traits\ResourceModel;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\CrawlerTranslate\Jobs\TranslateCrawlerContentJob;
use Juzaweb\Scrawler\Interfaces\CrawlerContentEntity;
use Juzaweb\Scrawler\Support\Traits\GetContentAttr;

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
 * @property-read CrawlerLink $link
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
 * @mixin Eloquent
 * @property int|null $resource_id
 * @method static Builder|CrawlerContent whereResourceId($value)
 * @property int $is_source
 * @property-read CrawlerContent $children
 * @method static Builder|CrawlerContent whereIsSource($value)
 * @property-read CrawlerPage $page
 * @property int|null $website_id
 * @property string|null $blogger_post_id
 * @property-read CrawlerWebsite|null $website
 * @method static Builder|CrawlerContent whereBloggerPostId($value)
 * @method static Builder|CrawlerContent whereWebsiteId($value)
 * @property-read Post|null $post
 * @method static Builder|CrawlerContent whereFilter($params = [])
 * @property int $site_id
 * @property string|null $deleted_at
 * @property mixed|null $category_ids
 * @property string $title
 * @property int|null $created_by
 * @property int|null $source_content_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Juzaweb\Scrawler\Models\AutoPosts\AutoPostWebsite> $autoPostWebsites
 * @property-read int|null $auto_post_websites_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Juzaweb\Crawler\Models\CustomCategory> $categories
 * @property-read int|null $categories_count
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Juzaweb\Scrawler\Models\TranslationLog> $translationLogs
 * @property-read int|null $translation_logs_count
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Crawler\Models\CrawlerContent whereCategoryIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Crawler\Models\CrawlerContent whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Crawler\Models\CrawlerContent whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Crawler\Models\CrawlerContent whereSiteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Crawler\Models\CrawlerContent whereSourceContentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Crawler\Models\CrawlerContent whereTitle($value)
 * @mixin \Eloquent
 */
class CrawlerContent extends Model implements CrawlerContentEntity
{
    use ResourceModel, GetContentAttr;

    public const STATUS_PENDING = 'pending';
    public const STATUS_DONE = 'done';
    public const STATUS_ERROR = 'error';
    public const STATUS_TRANSLATING = 'translating';
    public const STATUS_POSTTING = 'posting';
    public const STATUS_REGET = 'reget';

    protected $table = 'crawler_contents';

    protected string $fieldName = 'id';

    protected $fillable = [
        'components',
        'lang',
        'link_id',
        'page_id',
        'post_id',
        'resource_id',
        'website_id',
        'status',
        'is_source',
        'created_by',
        'source_content_id',
    ];

    public $casts = [
        'components' => 'array',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => trans('cms::app.pending'),
            self::STATUS_DONE => trans('crawler::content.done'),
            self::STATUS_ERROR => trans('cms::app.error'),
            self::STATUS_TRANSLATING => trans('crawler::content.translating'),
            self::STATUS_POSTTING => trans('crawler::content.posting'),
            self::STATUS_REGET => trans('crawler::content.reget'),
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(CrawlerWebsite::class, 'website_id', 'id');
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(CrawlerLink::class, 'link_id', 'id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(CrawlerPage::class, 'page_id', 'id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(__CLASS__, 'source_content_id', 'id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            CustomCategory::class,
            'crawler_content_category',
            'crawler_content_id',
            'crawler_category_id',
            'id',
            'id'
        );
    }

    public function reget(bool $sync = false): bool
    {
        if (!$this->is_source) {
            return true;
        }

        if ($sync) {
            app(CrawlerContract::class)->crawContentLink($this->link);

            $this->link->update(['status' => CrawlerLink::STATUS_DONE]);

            return true;
        }

        if ($this->link->status == CrawlerLink::STATUS_PENDING) {
            return true;
        }

        $this->link->update(['status' => CrawlerLink::STATUS_PENDING]);

        $this->update(['status' => CrawlerContent::STATUS_REGET]);

        return true;
    }

    public function retrans(): bool
    {
        $sourceContent = $this->sourceContent();

        if ($sourceContent == null) {
            return false;
        }

        $sourceContent->update(['status' => self::STATUS_TRANSLATING]);

        TranslateCrawlerContentJob::dispatch($sourceContent, $this->lang)->onQueue(
            config('crawler.queue.translate')
        );

        return true;
    }

    public function sourceContent(): CrawlerContent|null|static
    {
        if ($this->is_source) {
            return $this;
        }

        return self::query()->where(['link_id' => $this->link_id, 'is_source' => true])->first();
    }
}
