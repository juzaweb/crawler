<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Crawler\Contracts;

use Illuminate\Support\Collection;
use Juzaweb\Modules\Crawler\PoolCrawler;

/**
 * @see \Juzaweb\Modules\Crawler\CrawlerRepository
 */
interface Crawler
{
    public function registerDataType(string $key, callable $callback): void;

    public function getDataType(string $key): ?CrawlerDataType;

    public function getDataTypes(): array;

    public function crawl(Collection $pages): PoolCrawler;
}
