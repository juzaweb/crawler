<?php
/**
 * LARABIZ CMS - Full SPA Laravel CMS
 *
 * @package    larabizcms/larabiz
 * @author     The Anh Dang
 * @link       https://larabiz.com
 */

namespace JuzawebModulesCrawler\Elements;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;

abstract class BaseElement
{
    protected array $removeElements = [];

    public static function make(string $element, ?string $attribute = null, int $index = 0): static
    {
        return new static($element, $attribute, $index);
    }

    public function __construct(public string $element, public ?string $attribute = null, public int $index = 0)
    {
    }

    public function replateHtmlCharacters(string $html): string
    {
        return fix_html($html);
    }

    public function removeElements(array $elements): static
    {
        $this->removeElements = $this->parseElementsParam($elements);

        return $this;
    }

    public function handleRemoveElements(DomCrawler $crawler): void
    {
        foreach ($this->removeElements as $element) {
            $crawler->filter($element['element'])->each(
                function ($nodes, $index) use ($element) {
                    /** @var DomCrawler $nodes */
                    foreach ($nodes as $node) {
                        if (isset($element['index']) && $element['index'] != $index) {
                            continue;
                        }

                        $node->parentNode->removeChild($node);
                    }
                }
            );
        }
    }

    protected function parseElementsParam(array $elements): array
    {
        return array_map(
            function ($element) {
                if (is_array($element)) {
                    return $element;
                }

                return [
                    'element' => $element,
                    'index' => null,
                ];
            },
            $elements
        );
    }
}
