<?php

namespace Juzaweb\Crawler\Models;

use Juzaweb\CMS\Models\Model;

/**
 * Juzaweb\Crawler\Models\ContentResouce
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Crawler\Models\ContentResouce newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Crawler\Models\ContentResouce newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Crawler\Models\ContentResouce query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Crawler\Models\ContentResouce whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Crawler\Models\ContentResouce whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Crawler\Models\ContentResouce whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ContentResouce extends Model
{
    protected $table = 'crawler_content_resouces';
    protected $fillable = [];
}
