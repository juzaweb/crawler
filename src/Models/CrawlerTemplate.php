<?php

namespace Juzaweb\Crawler\Models;

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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Language|null $language
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereCustomClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereDataElements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereFilter($params = [])
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereLinkElement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CrawlerTemplate extends Model
{
    use ResourceModel;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DONE = 'done';
    const STATUS_ERROR = 'error';

    protected $table = 'crawler_templates';

    protected $fillable = [
        'link_element',
        'data_elements',
        'custom_class'
    ];

    public function language()
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
