<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    MIT
 */

namespace Juzaweb\Crawler\Support;

use Juzaweb\CMS\Support\HtmlDomNode;

class CrawlerElement
{
    public static string $VALUE_TEXT = 'text';

    public static string $VALUE_INNERTEXT = 'innertext';

    public static string $VALUE_OUTERTEXT = 'outertext';

    public string $selector;

    public ?int $index;

    public ?string $attr = null;

    protected string|array $element;

    public function __construct(string|array $element)
    {
        $this->element = $element;
        $this->selector = $this->getSelector();
        $this->index = $this->getIndex();
        $this->attr = $this->getAttribute();
    }

    public function getSelector(): string
    {
        if (is_string($this->element)) {
            return $this->element;
        }

        return (string) $this->element['selector'];
    }

    public function getIndex(): ?int
    {
        if (is_string($this->element)) {
            return 0;
        }

        return $this->element['index'] ?? null;
    }

    public function getValue(HtmlDomCrawler $domCrawler): null|array|string
    {
        $elements = $domCrawler->find(
            $this->selector,
            $this->index
        );

        if (empty($elements)) {
            return null;
        }

        if (is_null($this->index)) {
            $result = [];
            foreach ($elements as $item) {
                $result[] = $this->getHtmlNodeValue($item);
            }
        } else {
            $result = $this->getHtmlNodeValue($elements);
        }

        return $result;
    }

    public function getAttribute(): ?string
    {
        if (is_string($this->element)) {
            return null;
        }

        return $this->element['attr'] ?? null;
    }

    private function getHtmlNodeValue(HtmlDomNode $node)
    {
        if ($this->attr) {
            return $node->getAttribute($this->attr);
        }

        if (is_string($this->element)) {
            return $node->innertext();
        }

        $val = $this->element['value'] ?? 'innertext';

        if (is_callable($val)) {
            return call_user_func_array($val, [$node]);
        }

        if ($val == 'text') {
            return html_entity_decode($node->text());
        }

        if ($val == 'outertext') {
            return $node->outertext();
        }

        return $node->innertext();
    }
}
