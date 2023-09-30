<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Support\Selector;

use Juzaweb\Crawler\Interfaces\CrawlerElement;
use Juzaweb\Crawler\Interfaces\CrawlerSelector;
use Juzaweb\Crawler\Support\HtmlDomCrawler;

class XenforoCommentSelector implements CrawlerSelector
{
    public function getValue(CrawlerElement $element, HtmlDomCrawler $domCrawler): null|array|string
    {
        // TODO: Implement getValue() method.
    }
}
