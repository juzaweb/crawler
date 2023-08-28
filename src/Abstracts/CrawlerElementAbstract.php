<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Abstracts;

use Juzaweb\CMS\Support\HtmlDomNode;
use Juzaweb\Crawler\Support\Converter\BBCodeToHTML;
use Juzaweb\Crawler\Support\Converter\HTMLToBBCode;
use Juzaweb\Crawler\Support\HtmlDomCrawler;

abstract class CrawlerElementAbstract
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

        /* Convert to bbcode to remove HTML format */
        if (is_null($this->index)) {
            $result = [];
            foreach ($elements as $item) {
                $text = HTMLToBBCode::toBBCode($this->getHtmlNodeValue($item));
                $result[] = BBCodeToHTML::toHTML($text);
            }
        } else {
            $result = HTMLToBBCode::toBBCode($this->getHtmlNodeValue($elements));
            $result = BBCodeToHTML::toHTML($result);
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

    protected function getHtmlNodeValue(HtmlDomNode $node)
    {
        if ($this->attr) {
            return $node->getAttribute($this->attr);
        }

        if (is_string($this->element)) {
            return $node->innertext();
        }

        $val = $this->element['value'] ?? static::$VALUE_INNERTEXT;

        if (is_callable($val)) {
            return call_user_func_array($val, [$node]);
        }

        if ($val == static::$VALUE_TEXT) {
            return $node->text();
        }

        if ($val == static::$VALUE_OUTERTEXT) {
            return $node->outertext();
        }

        return $node->innertext();
    }
}
