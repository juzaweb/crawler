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

use Illuminate\Contracts\Support\Arrayable;

abstract class CrawlerTemplate implements Arrayable
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
        return $this->linkElement;
    }

    public function toArray(): array
    {
        return [
            'link_element' => $this->getLinkElement(),
            'link_element_attribute' => $this->getLinkElementAttribute(),
            'elements' => collect($this->getDataElements())->map(
                function ($element, $key) {
                    if ($key == 'data') {
                        $element = collect($element)->map(
                            function ($value, $name) {
                                $value['name'] = $name;
                                $value['selector'] = e($value['selector']);
                                $value['value'] = $value['value'] ?? 'html';
                                return $value;
                            }
                        )->values();
                    }

                    return $element;
                }
            ),
        ];
    }
}
