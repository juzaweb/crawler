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

class MediumCom extends CrawlerTemplate implements CrawlerTemplateInterface
{
    protected string $linkElement = 'article h2';

    public function getDataElements(): array
    {
        return [
            'data' => [
                'title' => [
                    'selector' => 'h1.pw-post-title',
                    'value' => CrawlerElement::$VALUE_TEXT,
                    'index' => 0,
                ],
                'content' => [
                    'selector' => 'article section',
                    'index' => 0,
                ],
                'thumbnail' => [
                    'selector' => 'article section img',
                    'attr' => 'src',
                    'index' => 0,
                ],
                'categories' => [
                    'selector' => '.ads.al',
                    'value' => CrawlerElement::$VALUE_TEXT,
                ]
            ]
        ];
    }
}
