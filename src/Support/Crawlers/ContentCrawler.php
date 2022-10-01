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
use Juzaweb\Crawler\Abstracts\CrawlerAbstract;
use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface as CrawlerTemplate;
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

        $result = [];
        $elementData = $template->getDataElements();

        if ($removes = Arr::get($elementData, 'removes', [])) {
            $this->removeElements($removes, $url, $contents);
        }

        foreach ($elementData['data'] ?? [] as $code => $el) {
            $element = new CrawlerElement($el);
            Arr::set($result, $code, $element->getValue($contents));
        }

        return $result;
    }

    public function getContensOfResource(string $url, CrawlerTemplate $template): array
    {
        if (!$template instanceof TemplateHasResource) {
            throw new \Exception('Template is not a instanceof '. TemplateHasResource::class);
        }

        $result = [];

        $contents = $this->createHTMLDomFromUrl($url);

        $elementData = $template->getDataResourceElements();

        foreach ($elementData as $key => $resource) {
            if ($removes = Arr::get($elementData, 'removes', [])) {
                $this->removeElements($removes, $url, $contents);
            }

            foreach ($resource['data'] ?? [] as $code => $el) {
                $element = new CrawlerElement($el);
                Arr::set($result, "$key.{$code}", $element->getValue($contents));
            }
        }

        return $result;
    }

    protected function removeElements(array $removes, string $url, HtmlDomCrawler &$contents): void
    {
        $domain = get_domain_by_url($url);

        foreach ($removes as $remove) {
            if ($remove == 'internal_link') {
                $contents->removeInternalLink($domain);
                continue;
            }

            if (is_string($remove)) {
                $selector = $remove;
                $type = 1;
            }

            if (is_array($remove)) {
                $selector = $remove['selector'];
                $type = (int) Arr::get($remove, 'type', 1);
            }

            $contents->removeElement($selector, $type);
        }
    }
}
