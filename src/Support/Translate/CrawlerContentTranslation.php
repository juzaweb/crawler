<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/juzacms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Helpers\Translate;

use Juzaweb\Crawler\Models\CrawlerContent;

class CrawlerContentTranslation
{
    public function __construct(protected CrawlerContent $content, protected string $source, protected string $target)
    {
    }

    public function translate(): array
    {
        $components = [];
        foreach ($this->content->components as $key => $component) {
            $translater = new TranslateBBCode($this->source, $this->target, $component);
            $components[$key] = $translater->translate();
        }

        return $components;
    }
}
