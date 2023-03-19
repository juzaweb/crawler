<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/juzacms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Support\Translate;

use Juzaweb\Crawler\Models\CrawlerContent;

class CrawlerContentTranslation
{
    protected array $translateComponents = [
        'title',
        'content'
    ];

    public function __construct(
        protected CrawlerContent $content,
        protected string $source,
        protected string $target
    ) {
    }

    public function translate(): array
    {
        $components = [];
        $replaces = $this->content->page->website->translate_replaces ?? [];

        foreach ($this->content->components as $key => $component) {
            if (!in_array($key, $this->translateComponents)) {
                $components[$key] = $component;
                continue;
            }

            $translater = new TranslateBBCode($this->source, $this->target, $component);
            $translate = $translater->translate();
            foreach ($replaces as $replace) {
                $translate = str_ireplace($replace['search'], $replace['replace'], $translate);
            }
            $components[$key] = $translate;
        }

        return $components;
    }
}
