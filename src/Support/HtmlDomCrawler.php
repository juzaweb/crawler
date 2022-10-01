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

use Juzaweb\CMS\Support\HtmlDom;
use Juzaweb\CMS\Support\HtmlDomNode;

class HtmlDomCrawler
{
    protected string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function find(string $selector, ?int $index = null): HtmlDom|HtmlDomNode|array|bool
    {
        $html = str_get_html($this->content);

        if (is_null($index)) {
            return @$html->find($selector);
        }

        return @$html->find($selector, $index);
    }

    public function plaintext(string $selector, int $index = 0): ?string
    {
        $plaintext = $this->find($selector, $index)->plaintext;

        return html_entity_decode($plaintext);
    }

    public function innertext(string $selector, int $index = 0): ?string
    {
        return $this->find($selector, $index)->innertext;
    }

    public function attribute(string $selector, int $value, int $index = 0)
    {
        return $this->find($selector, $index)->{$value};
    }

    public function removeElement(string $selector, int $type, int $index = null): void
    {
        $html = str_get_html($this->content);

        $contents = $html->find($selector, $index);

        if (!is_null($index)) {
            if ($type == 1) {
                $contents->outertext = '';
            }

            if ($type == 2) {
                $text = $contents->text();
                $contents->outertext = $text;
            }
        } else {
            foreach ($contents as $item) {
                if ($type == 1) {
                    $item->outertext = '';
                }

                if ($type == 2) {
                    $text = $item->text();
                    $item->outertext = $text;
                }
            }
        }

        $html->load($html->save());

        $this->content = $html->root->outertext;
    }

    public function removeScript(): void
    {
        $scripts = $this->find('script');
        foreach ($scripts as $script) {
            $this->content = str_replace(
                $script->outertext(),
                '',
                $this->content
            );
        }
    }

    public function removeInternalLink(string $domain): void
    {
        $html = str_get_html($this->content);
        $links = $html->find('a');

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

        $html->load($html->save());

        $this->content = $html->root->outertext;
    }
}
