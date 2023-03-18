<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/juzacms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Support\Templates;

use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface;
use Juzaweb\Crawler\Support\CrawlerElement;

class LaravelNewsCom extends CrawlerTemplate implements CrawlerTemplateInterface
{
    protected string $linkElement = '.grid .card a.flex';

    public function getDataElements(): array
    {
        return [
            'data' => [
                'title' => [
                    'selector' => 'header h1.tracking-tighter',
                    'value' => CrawlerElement::$VALUE_TEXT,
                    'index' => 0,
                ],
                'content' => [
                    'selector' => 'div.prose-sm',
                    'index' => 0,
                ],
                'thumbnail' => [
                    'selector' => 'meta[property="og:image"]',
                    'attr' => 'content',
                    'index' => 0,
                ],
                'categories' => [
                    'selector' => 'header .items-center a.transition-opacity',
                    'value' => CrawlerElement::$VALUE_TEXT,
                ]
            ]
        ];
    }
}
