<?php

namespace Juzaweb\Crawler\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Traits\ResourceModel;
use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface;
use Juzaweb\Crawler\Support\Templates\DatabaseTemplate;

/**
 * Juzaweb\Crawler\Models\CrawlerWebsite
 *
 * @property int $id
 * @property string $domain
 * @property int $has_ssl
 * @property int $template_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|CrawlerPage[] $pages
 * @property-read int|null $pages_count
 * @property-read CrawlerTemplate $template
 * @method static Builder|CrawlerWebsite newModelQuery()
 * @method static Builder|CrawlerWebsite newQuery()
 * @method static Builder|CrawlerWebsite query()
 * @method static Builder|CrawlerWebsite whereCreatedAt($value)
 * @method static Builder|CrawlerWebsite whereDomain($value)
 * @method static Builder|CrawlerWebsite whereHasSsl($value)
 * @method static Builder|CrawlerWebsite whereId($value)
 * @method static Builder|CrawlerWebsite whereTemplateId($value)
 * @method static Builder|CrawlerWebsite whereUpdatedAt($value)
 * @mixin Eloquent
 * @property int $active
 * @property string $template_class
 * @method static Builder|CrawlerWebsite whereActive($value)
 * @method static Builder|CrawlerWebsite whereFilter($params = [])
 * @method static Builder|CrawlerWebsite whereTemplateClass($value)
 * @property array|null $translate_replaces
 * @method static Builder|CrawlerWebsite whereTranslateReplaces($value)
 */
class CrawlerWebsite extends Model
{
    use ResourceModel;

    protected $table = 'crawler_websites';

    protected string $fieldName = 'domain';

    protected $fillable = [
        'domain',
        'has_ssl',
        'active',
        'template_id',
        'template_class',
        'translate_replaces',
    ];

    protected $casts = ['translate_replaces' => 'array'];

    public function pages(): HasMany
    {
        return $this->hasMany(CrawlerPage::class, 'website_id', 'id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CrawlerTemplate::class, 'template_id', 'id');
    }

    public function getTemplateClass(): CrawlerTemplateInterface
    {
        if ($this->template) {
            return app()->make(DatabaseTemplate::class, ['template' => $this->template]);
        }

        return app($this->template_class);
    }

    public function attributeLabels(): array
    {
        return [
            'template_class' => trans('crawler::content.template')
        ];
    }
}
