<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    MIT
 */

namespace Juzaweb\Crawler\Support\Traists;

use Juzaweb\Crawler\Models\CrawlerLink;
use Juzaweb\Crawler\Support\CrawlerElement;
use Juzaweb\Crawler\Support\Templates\CrawlerTemplate;

trait ContentCrawler
{
    public function crawLinkContent(CrawlerLink $link): bool
    {
        $template = $link->website->template->getTemplateClass();

        $components = $this->crawContentsUrl($link->url, $template);
    }

    public function crawContentsUrl(string $url, CrawlerTemplate $template): array
    {
        $contents = $this->createHTMLDomFromUrl($url);

        $contents->removeScript();

        $result = [];
        foreach ($template->getDataElements() as $code => $el) {
            $element = new CrawlerElement($el);
            $result[$code] = $element->getValue($contents);
        }

        return $result;
    }
}
