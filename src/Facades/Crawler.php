<?php

/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @author     The Anh Dang
 *
 * @link       https://cms.juzaweb.com
 *
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Crawler\Facades;

use Illuminate\Support\Facades\Facade;
use Juzaweb\Modules\Crawler\CrawlerRepository;

/**
 * @method static void registerDataType(string $key, callable $callback)
 * @method static \Juzaweb\Modules\Crawler\Contracts\CrawlerDataType|null getDataType(string $key)
 * @method static array getDataTypes()
 * @method static \Juzaweb\Modules\Crawler\PoolCrawler crawl(\Illuminate\Support\Collection $pages)
 *
 * @see CrawlerRepository
 */
class Crawler extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Juzaweb\Modules\Crawler\Contracts\Crawler::class;
    }
}
