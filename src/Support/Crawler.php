<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    MIT
 */

namespace Juzaweb\Crawler\Support;

use GuzzleHttp\Client;
use Juzaweb\CMS\Contracts\PostImporterContract;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Support\Traists\ContentCrawler;
use Juzaweb\Crawler\Support\Traists\LinkCrawler;

class Crawler implements CrawlerContract
{
    use LinkCrawler, ContentCrawler;

    protected PostImporterContract $postImport;

    public function __construct($postImport)
    {
        $this->postImport = $postImport;
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
        return new Client(['timeout' => 10]);
    }
}
