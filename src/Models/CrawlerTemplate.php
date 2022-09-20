<?php

namespace Juzaweb\Crawler\Models;

use Juzaweb\CMS\Models\Language;
use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Traits\ResourceModel;
use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface;
use Juzaweb\Crawler\Support\Templates\DatabaseTemplate;

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
