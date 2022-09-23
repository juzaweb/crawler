<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    MIT
 */

namespace Juzaweb\Crawler\Support\Traists;

use Juzaweb\Crawler\Models\CrawlerLink;

trait ContentCrawler
{
    public function crawLinkContent(CrawlerLink $link): bool
    {
        $template = $link->website->template->getTemplateClass();

        $contents = $this->createHTMLDomFromUrl($link->url);

        $contents->removeScript();

        $result = [];
        foreach ($template->getDataElements() as $component) {
            $elementContent = $contents->find(
                $component->element,
                $component->index
            );

            if (empty($elementContent)) {
                $result[$component->code] = '';
                continue;
            }

            if (is_null($component->index)) {
                $result[$component->code] = '';
                foreach ($elementContent as $item) {
                    if ($component->attr) {
                        $result[$component->code] .= $item->{$component->attr};
                    } else {
                        $innertext = $item->innertext();
                        if (empty($innertext)) {
                            $result[$component->code] .= '';
                            continue;
                        }

                        $result[$component->code] .= $innertext;
                    }
                }
            } else {
                if ($component->attr) {
                    $result[$component->code] = $elementContent->{$component->attr};
                } else {
                    $innertext = $elementContent->innertext();
                    if (empty($innertext)) {
                        $result[$component->code] .= '';
                    } else {
                        $result[$component->code] = $innertext;
                    }
                }
            }
        }
    }
}
