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

class Xenforo1 extends CrawlerTemplate implements CrawlerTemplateInterface
{
    protected string $linkElement = '.discussionListItem a.PreviewTooltip';

    public function getDataElements(): array
    {
        return [
            'data' => [
                'title' => [
                    'selector' => '.titleBar h1',
                ]
            ]
        ];
    }
}
