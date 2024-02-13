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
use Juzaweb\Crawler\Support\CrawlerElements\XenforoCommentCrawlerElement;

class Xenforo extends CrawlerTemplate implements CrawlerTemplateInterface
{
    protected string $linkElement = '.structItem-title a[data-xf-init="preview-tooltip"]';

    public function getDataElements(): array
    {
        return [
            'data' => [
                'title' => [
                    'selector' => 'h1.p-title-value',
                    'value' => CrawlerElement::$VALUE_TEXT,
                    'index' => 0,
                    'removes' => [
                        '.label',
                        '.label-append',
                    ]
                ],
                'content' => [
                    'selector' => '.message-body .bbWrapper',
                    'index' => 0,
                ],
                'thumbnail' => [
                    'selector' => 'meta[property="og:image"]',
                    'attr' => 'content',
                    'index' => 0,
                ],
                'author' => [
                    'selector' => '.username',
                    'index' => 0,
                ],
                'comments' => [
                    'selector' => '.message-body .bbWrapper',
                    'skip_indexs' => 0,
                    'crawler_element' => XenforoCommentCrawlerElement::class,
                ],
                'tags' => [
                    'selector' => '.js-tagList a',
                    'value' => CrawlerElement::$VALUE_TEXT,
                ],
            ],
            'removes' => [
                'script',
                '.bbCodeBlock-expandLink',
                '.bbCodeBlock-title',
                'img.smilie',
            ],
            'replaces' => [
                'http/' => 'http://',
            ],
            'page_regex' => '(.*)\/forums\/([a-z0-9\-]+)\.([0-9]+)\/',
            'page_suffix' => 'page-{page}',
        ];
    }
}
