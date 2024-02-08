<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Models\User;

/**
 * Juzaweb\Scrawler\Models\CustomCategory
 *
 * @property int $id
 * @property string $name
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Juzaweb\CMS\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Scrawler\Models\CustomCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Scrawler\Models\CustomCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Scrawler\Models\CustomCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Scrawler\Models\CustomCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Scrawler\Models\CustomCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Scrawler\Models\CustomCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Scrawler\Models\CustomCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Juzaweb\Scrawler\Models\CustomCategory whereUserId($value)
 * @mixin \Eloquent
 */
class CustomCategory extends Model
{
    protected $table = 'crawler_custom_categories';

    protected $fillable = [
        'name',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
