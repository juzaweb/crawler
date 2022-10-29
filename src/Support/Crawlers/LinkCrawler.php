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
use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface as CrawlerTemplate;
use Juzaweb\Crawler\Interfaces\TemplateHasResource;

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
                $template->getLinkElement(),
                $template->getLinkElementAttribute()
            );
        }

        if (!$template instanceof TemplateHasResource) {
            throw new \Exception('Template is not a instanceof '. TemplateHasResource::class);
        }

        return $this->crawLinkViaElement(
            $url,
            $template->getLinkResourceElement(),
            $template->getLinkElementAttribute()
        );
    }

    public function crawLinkViaElement(
        string $url,
        string $element,
        string $elementAttribute = 'href'
    ): array {
        $html = $this->createHTMLDomFromUrl($url);

        $urls = $html->find($element);

        if (empty($urls)) {
            return [];
        }

        $items = [];
        foreach ($urls as $url) {
            $href = $url->getAttribute($elementAttribute);

            $items[] = trim(get_full_url($href, $url));
        }

        return $items;
    }
}
