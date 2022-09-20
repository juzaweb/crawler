<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    MIT
 */

namespace Juzaweb\Crawler\Support\Templates;

abstract class CrawlerTemplate
{
    protected string $linkElement;

    protected string $linkElementAttribute = 'href';

    abstract public function getDataElements(): array;

    public function getLinkElementAttribute(): string
    {
        return $this->linkElementAttribute;
    }

    public function getLinkElement(): string
    {
        return $this->getLinkElement();
    }
}
