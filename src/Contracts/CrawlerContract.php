<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    MIT
 */

namespace Juzaweb\Crawler\Contracts;

use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface as CrawlerTemplate;
use Juzaweb\Crawler\Models\CrawlerContent;
use Juzaweb\Crawler\Models\CrawlerLink;
use Juzaweb\Crawler\Models\CrawlerPage;

/**
 * @see \Juzaweb\Crawler\Support\Crawler
 */
interface CrawlerContract
{
    /**
     * @param  CrawlerPage  $page
     * @param  int  $pageNumber
     * @param  string|array|null  $proxy
     * @return bool|int
     * @see \Juzaweb\Crawler\Support\Crawler::crawPageLinks()
     */
    public function crawPageLinks(CrawlerPage $page, int $pageNumber, string|array|null $proxy = null): bool|int;

    /**
     * @param  string  $url
     * @param  CrawlerTemplate  $template
     * @return array
     * @see \Juzaweb\Crawler\Support\Crawler::crawLinksUrl()
     */
    public function crawLinksUrl(string $url, CrawlerTemplate $template): array;

    public function crawContentUrl(string $url, CrawlerTemplate $template, bool $isResource = false): array;

    /**
     * @param  CrawlerLink  $link
     * @param  string|array|null  $proxy
     * @return CrawlerContent
     * @see \Juzaweb\Crawler\Support\Crawler::crawContentLink()
     */
    public function crawContentLink(CrawlerLink $link, string|array|null $proxy = null): CrawlerContent;

    public function savePost(CrawlerContent $content);

    public function checkAndInsertLinks(array $items, CrawlerPage $page): array;
}
