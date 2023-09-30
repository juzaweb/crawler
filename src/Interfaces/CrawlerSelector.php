<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Interfaces;

use Juzaweb\Crawler\Support\HtmlDomCrawler;

interface CrawlerSelector
{
    public function getValue(CrawlerElement $element, HtmlDomCrawler $domCrawler): null|array|string;
}
