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

use Juzaweb\CMS\Support\HtmlDomNode;
use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface;
use Juzaweb\Crawler\Interfaces\TemplateHasResource;
use Juzaweb\Crawler\Support\CrawlerElement;
use Juzaweb\Crawler\Support\Traists\UseTemplateHasResource;

class TruyenFullVN extends CrawlerTemplate implements CrawlerTemplateInterface, TemplateHasResource
{
    use UseTemplateHasResource;

    protected string $linkElement = 'h3.truyen-title a';

    protected string $resourceUrlWithPage = '{post_url}/trang-{page}/';

    public function getDataElements(): array
    {
        return [
            'data' => [
                'title' => 'h3.title',
                'content' => '.desc-text',
                'thumbnail' => [
                    'selector' => '.books .book img',
                    'attr' => 'src',
                    'index' => 0
                ],
                'meta.source' => '.info .source',
                'authors' => [
                    'selector' => '.info a[itemprop="author"]',
                    'value' => CrawlerElement::$VALUE_TEXT,
                ],
                'genres' => [
                    'selector' => '.info a[itemprop="genre"]',
                    'value' => CrawlerElement::$VALUE_TEXT,
                ]
            ]
        ];
    }

    public function getLinkResourceElement(): string
    {
        return '.list-chapter a';
    }

    public function getDataResourceElements(): array
    {
        return [
            'chapters' => [
                'data' => [
                    'name' => [
                        'selector' => '.chapter-title',
                        'value' => CrawlerElement::$VALUE_TEXT,
                        'index' => 0
                    ],
                    'meta.content' => '#chapter-c',
                ],
                'removes' => [
                    '#ads-chapter-pc-top'
                ]
            ]
        ];
    }
}
