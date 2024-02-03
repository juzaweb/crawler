<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    MIT
 */

namespace Juzaweb\Crawler\Abstracts;

use Juzaweb\Crawler\Support\HtmlDomCrawler;

abstract class CrawlerAbstract
{
    protected string|array|null $proxy = null;

    public function withProxy(string|array|null $proxy): static
    {
        $this->proxy = $proxy;

        return $this;
    }

    protected function createHTMLDomFromUrl($url): HtmlDomCrawler
    {
        $contents = $this->getContentUrl($url);

        return $this->createHTMLDomFromContent($contents);
    }

    protected function createHTMLDomFromContent(string $content): HtmlDomCrawler
    {
        return new HtmlDomCrawler($content);
    }

    protected function getContentUrl($url): string
    {
        $response = makeCrawlerRequest($this->proxy)
            ->retry(3, 1000)
            ->get($url);

        return $response->body();
    }
}
