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

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
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
        $response = $this->getClient()->retry(3, 1000)->get($url);

        return $response->throw()->body();
    }

    protected function getClient(): PendingRequest
    {
        if ($this->proxy) {
            return Http::withOptions(['proxy' => $this->proxy])->timeout(20)->connectTimeout(10);
        }

        return Http::timeout(20)->connectTimeout(10);;
    }
}
