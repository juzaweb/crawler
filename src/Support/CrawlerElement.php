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

use Illuminate\Support\Arr;
use Juzaweb\CMS\Support\HtmlDomNode;
use Juzaweb\Crawler\Interfaces\CrawlerElement as CrawlerElementInterface;
use Juzaweb\Crawler\Support\Converter\BBCodeToHTML;
use Juzaweb\Crawler\Support\Converter\HTMLToBBCode;

class CrawlerElement implements CrawlerElementInterface
{
    public static string $VALUE_TEXT = 'text';
    public static string $VALUE_INNERTEXT = 'innertext';
    public static string $VALUE_OUTERTEXT = 'outertext';

    public string $selector;
    public ?int $index;
    public ?string $attr = null;
    public array $removes = [];
    public array $skipIndexs = [];

    protected string|array $element;

    public function __construct(string|array $element, protected string $url)
    {
        $this->element = $element;
        $this->selector = $this->getSelector();
        $this->index = $this->getIndex();
        $this->attr = $this->getAttribute();
        $this->removes = $this->getRemoves();
        $this->skipIndexs = $this->getSkipIndexs();
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

    public function getRemoves(): array
    {
        if (is_string($this->element)) {
            return [];
        }

        return $this->element['removes'] ?? [];
    }

    public function getSkipIndexs()
    {
        $skips = $this->element['skip_indexs'] ?? [];
        if (!is_array($skips)) {
            $skips = [$skips];
        }

        return $skips;
    }

    public function getValue(HtmlDomCrawler $domCrawler): null|array|string
    {
        $domCrawler->removeScript();

        if ($this->removes) {
            $this->removeElements($domCrawler);
        }

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
            foreach ($elements as $index => $item) {
                if (in_array($index, $this->skipIndexs)) {
                    continue;
                }

                $text = HTMLToBBCode::toBBCode($this->getHtmlNodeValue($item));
                if (empty($text)) {
                    continue;
                }

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

    protected function removeElements(HtmlDomCrawler $contents): void
    {
        foreach ($this->removes as $remove) {
            $selector = $remove;
            $type = 1;
            if (is_array($remove)) {
                $selector = $remove['selector'];
                $type = (int) Arr::get($remove, 'type', 1);
            }

            $contents->removeElement($selector, $type);
        }
    }

    protected function getHtmlNodeValue(HtmlDomNode $node)
    {
        if ($this->attr) {
            return $node->getAttribute($this->attr);
        }

        if (is_string($this->element)) {
            $this->removeInternalLink($node);
            return $node->innertext();
        }

        $val = $this->element['value'] ?? static::$VALUE_INNERTEXT;

        if (is_callable($val)) {
            return call_user_func($val, $node);
        }

        if ($val == static::$VALUE_TEXT) {
            return $node->text();
        }

        $this->removeInternalLink($node);
        if ($val == static::$VALUE_OUTERTEXT) {
            return $node->outertext();
        }

        return $node->innertext();
    }

    protected function removeInternalLink(HtmlDomNode $node): void
    {
        $domain = get_domain_by_url($this->url);

        $links = $node->find('a');

        foreach ($links as $item) {
            if (is_url($item->href)) {
                if ($domain == get_domain_by_url($item->href)) {
                    $text = $item->text();
                    $item->outertext = $text;
                }

                continue;
            }

            if (str_contains($item->href, '/url?q=')) {
                $href = str_replace('/url?q=', '', $item->href);
                $href = urldecode($href);
                $href = base64_decode($href);

                if (is_url($href)) {
                    $text = '<a href="'. $href .'">'. $item->text() .'</a>';
                } else {
                    $text = $item->text();
                }
            } else {
                $text = $item->text();
            }

            $item->outertext = $text;
        }

        $imgs = $node->find('img');
        foreach ($imgs as $item) {
            if (is_url($item->src)) {
                continue;
            }

            $item->outertext = str_replace($item->src, get_full_url($item->src, $this->url), $item->outertext);
        }
    }
}
