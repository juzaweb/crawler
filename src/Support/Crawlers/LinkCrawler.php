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

use Juzaweb\Crawler\Abstracts\CrawlerAbstract;
use Juzaweb\Crawler\Exceptions\HtmlDomCrawlerException;
use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface as CrawlerTemplate;
use Juzaweb\Crawler\Interfaces\TemplateHasClean;
use Juzaweb\Crawler\Interfaces\TemplateHasResource;
use RuntimeException;

class LinkCrawler extends CrawlerAbstract
{
    public function crawLinksUrl(
        string $url,
        CrawlerTemplate $template,
        bool $isResource = false
    ): array {
        if (!$isResource) {
            return $this->crawLinkViaElement(
                $url,
                $template,
                $template->getLinkElement(),
                $template->getLinkElementAttribute()
            );
        }

        if (!$template instanceof TemplateHasResource) {
            throw new RuntimeException('Template is not a instanceof '.TemplateHasResource::class);
        }

        return $this->crawLinkViaElement(
            $url,
            $template,
            $template->getLinkResourceElement(),
            $template->getLinkElementAttribute()
        );
    }

    public function crawLinkViaElement(
        string $url,
        CrawlerTemplate $template,
        string $element,
        string $elementAttribute = 'href'
    ): array {
        $html = $this->createHTMLDomFromUrl($url);

        if ($template instanceof TemplateHasClean) {
            $template->clean($html);
        }

        try {
            $elements = $html->find($element);
        } catch (HtmlDomCrawlerException $e) {
            throw new HtmlDomCrawlerException(
                $e->getMessage()." in {$url}",
            );
        }

        if (empty($elements)) {
            return [];
        }

        $items = [];
        foreach ($elements as $element) {
            $href = $element->getAttribute($elementAttribute);
            $items[] = trim(get_full_url($href, $url));
        }

        return $items;
    }
}
