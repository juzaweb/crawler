<?php

namespace Juzaweb\Crawler\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Juzaweb\Crawler\Models\CrawRemoveElement
 *
 * @property int $id
 * @property string $element
 * @property int $template_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|CrawRemoveElement newModelQuery()
 * @method static Builder|CrawRemoveElement newQuery()
 * @method static Builder|CrawRemoveElement query()
 * @method static Builder|CrawRemoveElement whereCreatedAt($value)
 * @method static Builder|CrawRemoveElement whereElement($value)
 * @method static Builder|CrawRemoveElement whereId($value)
 * @method static Builder|CrawRemoveElement whereTemplateId($value)
 * @method static Builder|CrawRemoveElement whereUpdatedAt($value)
 * @mixin Eloquent
 * @property int|null $index
 * @property-read CrawlerTemplate|null $template
 * @method static Builder|CrawRemoveElement whereIndex($value)
 * @property int $type 1: Remove all, 2: Remove html
 * @method static Builder|CrawRemoveElement whereType($value)
 */
class CrawRemoveElement extends Model
{
    protected $table = 'crawler_remove_elements';
    protected $fillable = [
        'element',
        'template_id',
        'index',
        'type',
    ];

    public $timestamps = false;

    public function template(): BelongsTo
    {
        return $this->belongsTo(CrawlerTemplate::class, 'template_id', 'id');
    }
}
