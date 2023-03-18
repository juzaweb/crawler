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
use Juzaweb\Crawler\Models\CrawlerPage;

interface CrawlerContract
{
    public function crawPageLinks(CrawlerPage $page): bool|int;

    public function crawLinksUrl(string $url, CrawlerTemplate $template): array;

    public function crawContentUrl(string $url, CrawlerTemplate $template, bool $isResource = false): array;

    public function savePost(CrawlerContent $content);
}
