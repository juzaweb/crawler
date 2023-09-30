<?php

namespace Juzaweb\Crawler\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Juzaweb\CMS\Models\Language;
use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Traits\ResourceModel;
use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface;
use Juzaweb\Crawler\Support\Templates\DatabaseTemplate;

/**
 * Juzaweb\Crawler\Models\CrawlerTemplate
 *
 * @property int $id
 * @property string $name
 * @property string|null $link_element
 * @property mixed|null $data_elements
 * @property string|null $custom_class
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Language|null $language
 * @method static Builder|CrawlerTemplate newModelQuery()
 * @method static Builder|CrawlerTemplate newQuery()
 * @method static Builder|CrawlerTemplate query()
 * @method static Builder|CrawlerTemplate whereCreatedAt($value)
 * @method static Builder|CrawlerTemplate whereCustomClass($value)
 * @method static Builder|CrawlerTemplate whereDataElements($value)
 * @method static Builder|CrawlerTemplate whereFilter($params = [])
 * @method static Builder|CrawlerTemplate whereId($value)
 * @method static Builder|CrawlerTemplate whereLinkElement($value)
 * @method static Builder|CrawlerTemplate whereName($value)
 * @method static Builder|CrawlerTemplate whereUpdatedAt($value)
 * @mixin Eloquent
 */
class CrawlerTemplate extends Model
{
    use ResourceModel;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_DONE = 'done';
    public const STATUS_ERROR = 'error';

    protected $table = 'crawler_templates';

    protected $fillable = [
        'name',
        'link_element',
        'data_elements',
        'custom_class',
    ];

    protected $casts = [
        'data_elements' => 'array',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang', 'code');
    }

    public function getTemplateClass(): CrawlerTemplateInterface
    {
        if ($this->custom_class) {
            return app($this->custom_class);
        }

        return app(DatabaseTemplate::class, [$this]);
    }
}
