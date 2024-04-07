<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Events;

use Juzaweb\Crawler\Interfaces\CrawlerContentEntity;

class ContentUpdated
{
    public function __construct(public CrawlerContentEntity $content)
    {
    }
}
