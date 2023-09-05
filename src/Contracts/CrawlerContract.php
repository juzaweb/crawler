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
    public function crawPageLinks(CrawlerPage $page, string|array|null $proxy = null): bool|int;

    public function crawLinksUrl(string $url, CrawlerTemplate $template): array;

    public function crawContentUrl(string $url, CrawlerTemplate $template, bool $isResource = false): array;

    public function crawContentLink(CrawlerLink $link, string|array|null $proxy = null): CrawlerContent;

    public function savePost(CrawlerContent $content);

    public function translate(CrawlerContent $content, string $target, string|array|null $proxy = null): CrawlerContent;

    public function translateCrawlerContent(CrawlerContent $content, string $target): array;

    public function checkAndInsertLinks(array $items, CrawlerPage $page): array;
}
