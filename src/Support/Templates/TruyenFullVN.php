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
use Juzaweb\Crawler\Support\CrawlerElement;

class TruyenFullVN extends CrawlerTemplate implements CrawlerTemplateInterface
{
    protected string $linkElement = 'h3.truyen-title a';

    public function getDataElements(): array
    {
        return [
            'title' => 'h3.title',
            'content' => '.desc-text',
            'thumbnail' => [
                'selector' => '.books .book img',
                'attr' => 'src',
                'index' => 0
            ],
            'meta[source]' => '.info .source',
            'authors' => [
                'selector' => '.info a[itemprop="author"]',
                'value' => CrawlerElement::$VALUE_TEXT,
            ],
            'genres' => [
                'selector' => '.info a[itemprop="genre"]',
                'value' => CrawlerElement::$VALUE_TEXT,
            ]
        ];
    }
}
