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
        'content',
        'comments',
    ];

    protected string|array $proxy;

    public function __construct(
        protected CrawlerContent $content,
        protected string $source,
        protected string $target
    ) {
    }

    public function withProxy(string|array $proxy): static
    {
        $this->proxy = $proxy;

        return $this;
    }

    public function translate(): array
    {
        $components = [];
        foreach ($this->content->components as $key => $component) {
            if (!in_array($key, $this->translateComponents)) {
                $components[$key] = $component;
                continue;
            }

            if (is_array($component)) {
                $components[$key] = $this->translateContents($component);
            } else {
                $components[$key] = $this->translateContent($component);
            }
        }

        return $components;
    }

    protected function translateContents(array $texts): array
    {
        $results = [];
        foreach ($texts as $key => $text) {
            $results[$key] = $this->translateContent($text);
        }

        return $results;
    }

    protected function translateContent(?string $text): ?string
    {
        if (empty($text)) {
            return $text;
        }

        $replaces = $this->content->page->website->translate_replaces ?? [];
        $searchs = collect($replaces)->pluck('search')->map(fn($item) => "/{$item}/ui")->toArray();
        $replaces = collect($replaces)
            ->mapWithKeys(fn($item) => [getReplaceSearchKey($item['search']) => $item['replace']])
            ->toArray();

        $translater = new TranslateBBCode($this->source, $this->target, $text);
        if (isset($this->proxy)) {
            $translater->withProxy($this->proxy);
        }

        $translate = $translater->translate();

        if ($searchs && $replaces) {
            $translate = replaceTranslate($searchs, $replaces, $translate, $count);
        }

        return $translate;
    }
}
