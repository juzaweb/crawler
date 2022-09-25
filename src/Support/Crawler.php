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
use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface as CrawlerTemplate;
use Juzaweb\Crawler\Models\CrawlerPage;
use Juzaweb\Crawler\Support\Crawlers\ContentCrawler;
use Juzaweb\Crawler\Support\Crawlers\LinkCrawler;

class Crawler implements CrawlerContract
{
    protected ContentCrawler $contentCrawler;

    protected LinkCrawler $linkCrawler;

    public function __construct()
    {
        $this->contentCrawler = app(ContentCrawler::class);

        $this->linkCrawler = app(LinkCrawler::class);
    }

    public function crawPageLinks(CrawlerPage $page): bool|int
    {
        return $this->linkCrawler->crawPageLinks($page);
    }

    public function crawLinksUrl(string $url, CrawlerTemplate $template): array
    {
        return $this->linkCrawler->crawLinksUrl($url, $template);
    }
}
