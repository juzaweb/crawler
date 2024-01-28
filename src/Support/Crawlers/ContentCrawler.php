<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    MIT
 */

namespace Juzaweb\Crawler\Support\Crawlers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Juzaweb\Crawler\Abstracts\CrawlerAbstract;
use Juzaweb\Crawler\Exceptions\ContentCrawlerException;
use Juzaweb\Crawler\Exceptions\HtmlDomCrawlerException;
use Juzaweb\Crawler\Interfaces\CrawlerElement as CrawlerElementInterface;
use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface as CrawlerTemplate;
use Juzaweb\Crawler\Interfaces\TemplateHasClean;
use Juzaweb\Crawler\Interfaces\TemplateHasResource;
use Juzaweb\Crawler\Support\CrawlerElement;
use Juzaweb\Crawler\Support\HtmlDomCrawler;

class ContentCrawler extends CrawlerAbstract
{
    public function crawContentsUrl(
        string $url,
        CrawlerTemplate $template,
        bool $isResource = false
    ): array {
        if ($isResource) {
            return $this->getContensOfResource($url, $template);
        }

        return $this->getContentsOfPost($url, $template);
    }

    public function getContentsOfPost(string $url, CrawlerTemplate $template): array
    {
        $contents = $this->createHTMLDomFromUrl($url);

        if ($template instanceof TemplateHasClean) {
            $template->clean($contents);
        }

        $result = [];
        $elementData = $template->getDataElements();

        if ($removes = Arr::get($elementData, 'removes', [])) {
            $this->removeElements($removes, $contents, $url);
        }

        foreach ($elementData['data'] ?? [] as $code => $el) {
            $element = $this->createCrawlerElement($el, $url);
            $value = $element->getValue($contents);

            if ($code === 'tags') {
                $value = $this->tagsFilter($value);
            }

            Arr::set($result, $code, $value);
        }

        return $result;
    }

    public function getContensOfResource(string $url, CrawlerTemplate $template): array
    {
        if (!$template instanceof TemplateHasResource) {
            throw new ContentCrawlerException('Template is not a instanceof ['. TemplateHasResource::class .']');
        }

        $result = [];
        $contents = $this->createHTMLDomFromUrl($url);

        if ($template instanceof TemplateHasClean) {
            $template->clean($contents);
        }

        $elementData = $template->getDataResourceElements();

        if ($removes = Arr::get($elementData, 'removes', [])) {
            $this->removeElements($removes, $contents, $url);
        }

        foreach ($elementData as $key => $resource) {
            if ($removes = Arr::get($resource, 'removes', [])) {
                $this->removeElements($removes, $contents, $url);
            }

            foreach ($resource['data'] ?? [] as $code => $el) {
                $element = $this->createCrawlerElement($el, $url);
                Arr::set($result, "{$key}.{$code}", $element->getValue($contents));
            }
        }

        return $result;
    }

    protected function tagsFilter(null|array|string $value): array|string|null
    {
        if ($value === null) {
            return null;
        }

        $removes = ['#'];

        if (is_string($value)) {
            return Str::replace($removes, '', $value);
        }

        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->tagsFilter($val);
            }

            return $value;
        }

        return $value;
    }

    protected function removeElements(array $removes, HtmlDomCrawler $contents, ?string $url = null): void
    {
        foreach ($removes as $remove) {
            $selector = $remove;
            $type = 1;
            if (is_array($remove)) {
                $selector = $remove['selector'];
                $type = (int) Arr::get($remove, 'type', 1);
            }

            try {
                $contents->removeElement($selector, $type);
            } catch (HtmlDomCrawlerException $e) {
                throw new ContentCrawlerException($e->getMessage() . " Link {$url}");
            }
        }
    }

    protected function createCrawlerElement(string|array $el, string $url): CrawlerElementInterface
    {
        if ($crawler = Arr::get($el, 'crawler_element')) {
            return app($crawler, ['element' => $el, 'url' => $url]);
        }

        return new CrawlerElement($el, $url);
    }
}
