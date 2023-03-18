<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/juzacms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Interfaces;

use Juzaweb\Crawler\Support\HtmlDomCrawler;

interface CrawlerElement
{
    public function getSelector(): string;

    public function getIndex(): ?int;

    public function getValue(HtmlDomCrawler $domCrawler): null|array|string;

    public function getAttribute(): ?string;
}
