<?php

namespace Juzaweb\Crawler\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Juzaweb\Crawler\Models\CrawlerPage[] $pages
 * @property-read int|null $pages_count
 * @property-read \Juzaweb\Crawler\Models\CrawlerTemplate $template
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerWebsite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerWebsite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerWebsite query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerWebsite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerWebsite whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerWebsite whereHasSsl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerWebsite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerWebsite whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrawlerWebsite whereUpdatedAt($value)
 * @mixin \Eloquent
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
        'template_class'
    ];

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
            return app(DatabaseTemplate::class, [$this->template]);
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
