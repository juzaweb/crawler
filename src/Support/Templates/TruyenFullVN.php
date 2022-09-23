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

use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface;

class TruyenFullVN extends CrawlerTemplate implements CrawlerTemplateInterface
{
    protected string $linkElement = 'h3.truyen-title a';

    public function getDataElements(): array
    {
        return [
            'title' => 'h3.title',
            'content' => '.desc-text',
            'meta[source]' => '.info .source',
            'authors' => [
                'selector' => '.info a[itemprop="author"]',
                'value' => 'text',
            ],
            'genres' => [
                'selector' => '.info a[itemprop="genre"]',
                'value' => 'text',
            ]
        ];
    }
}
