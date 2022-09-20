<?php

namespace Juzaweb\Crawler\Models;

use Juzaweb\CMS\Models\Language;
use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Traits\ResourceModel;

/**
 * Juzaweb\Crawler\ModelsTemplate
 *
 * @property int $id
 * @property string $name
 * @property string|null $crawler_thumbnail
 * @property string|null $crawler_title
 * @property string|null $crawler_content
 * @property string $lang
 * @property int $auto_leech
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Juzaweb\Crawler\Models\CrawlerPage[] $pages
 * @property-read int|null $pages_count
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereAutoCraw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereCrawContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereCrawThumbnail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereCrawTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\Juzaweb\Crawler\Models\Component[] $components
 * @property-read int|null $components_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Juzaweb\Crawler\Models\CrawLink[] $links
 * @property-read int|null $links_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Juzaweb\Crawler\Models\CrawRemoveElement[] $removes
 * @property-read int|null $removes_count
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereAutoLeech($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereCrawlerContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereCrawlerThumbnail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereCrawlerTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereFilter($params = [])
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereSiteId($value)
 * @property-read Language|null $language
 * @property int|null $user_id
 * @property string|null $post_status
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate wherePostStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerTemplate whereUserId($value)
 */
class CrawlerTemplate extends Model
{
    use ResourceModel;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DONE = 'done';
    const STATUS_ERROR = 'error';

    protected $table = 'crawler_templates';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function pages()
    {
        return $this->hasMany(CrawlerPage::class, 'template_id', 'id');
    }

    public function links()
    {
        return $this->hasMany(CrawLink::class, 'template_id', 'id');
    }

    public function components()
    {
        return $this->hasMany(Component::class, 'template_id', 'id');
    }

    public function removes()
    {
        return $this->hasMany(CrawRemoveElement::class, 'template_id', 'id');
    }

    public function language()
    {
        return $this->hasOne(Language::class, 'code', 'lang');
    }

    public function getTemplateClass()
    {

    }
}
