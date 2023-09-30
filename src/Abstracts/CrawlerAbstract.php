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

use GuzzleHttp\Client;
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
        $response = $this->getClient()->get($url);

        return $response->getBody()->getContents();
    }

    protected function getClient(): Client
    {
        $options = ['timeout' => 20, 'connect_timeout' => 10];
        if ($this->proxy) {
            $options['proxy'] = $this->proxy;
        }

        return new Client($options);
    }
}
