<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Support\Templates;

use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface;
use Juzaweb\Crawler\Support\CrawlerElement;

class Rawstory extends CrawlerTemplate implements CrawlerTemplateInterface
{
    protected string $linkElement = 'h2 .widget__headline-text';

    public function getDataElements(): array
    {
        return [
            'data' => [
                'title' => [
                    'selector' => 'h1.widget__headline',
                    'value' => CrawlerElement::$VALUE_TEXT,
                    'index' => 0,
                ],
                'content' => [
                    'selector' => '.body-description',
                    'index' => 0,
                ],
                'thumbnail' => [
                    'selector' => 'meta[property="og:image"]',
                    'attr' => 'content',
                    'index' => 0,
                ],
            ],
            'removes' => [
                'script',
                '.container_proper-ad-unit'
            ],
        ];
    }
}
