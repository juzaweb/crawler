<?php
/**
 * LARABIZ CMS - Full SPA Laravel CMS
 *
 * @package    larabizcms/larabiz
 * @author     The Anh Dang
 * @link       https://larabiz.com
 */

namespace Juzaweb\Modules\Crawler\Elements;

use Illuminate\Support\Arr;
use Juzaweb\Modules\Crawler\Contracts\Element;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class HtmlElement extends BaseElement implements Element
{
    protected bool $removeInternalLinks = false;

    protected ?string $baseUrl = null;

    protected bool $removeScript = true;

    public function getValueFrom(string $html): string
    {
        $html = preg_replace(
            '/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>\s*<img\s+[^>]*src=["\']\1["\']([^>]*)>\s*<\/a>/i',
            '<img src="$1" />',
            $html
        );

        $crawler = new DomCrawler($html);

        $contents = $crawler->filter($this->element)->eq($this->index)->html();

        if ($this->removeScript) {
            $contents = preg_replace('/<script.*?>.*?<\/script>/s', '', $contents);
            $contents = preg_replace('/<noscript.*?>.*?<\/noscript>/s', '', $contents);
        }

        $dom = new DomCrawler($contents);

        $this->handleRemoveElements($dom);

        $this->replaceImgs($dom);

        if ($this->removeInternalLinks) {
            $dom->filter('a')->each(
                function ($node) {
                    /** @var DomCrawler $node */
                    $href = $node->attr('href');
                    if (!$href || cr_is_internal_url($href, $this->baseUrl)) {
                        // $html = fix_html($node->html());
                        /** @var \DOMNode $element */
                        $element = $node->getNode(0);

                        // $newTextNode = $element->ownerDocument->createTextNode($html);
                        // $element->parentNode->replaceChild($newTextNode, $element);

                        foreach (iterator_to_array($element->childNodes) as $newNode) {
                            $importedNode = $element->ownerDocument->importNode($newNode, true);
                            $element->parentNode->insertBefore($importedNode, $element);
                        }

                        // Finally, remove the original element
                        $element->parentNode->removeChild($element);
                    }
                }
            );
        }

        return trim($this->replateHtmlCharacters($dom->html()));
    }

    public function removeInternalLinks(string $baseUrl): static
    {
        $this->removeInternalLinks = true;

        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function removeScript(bool $removeScript = true): static
    {
        $this->removeScript = $removeScript;

        return $this;
    }

    public function withIndex(int $index): static
    {
        $this->index = $index;

        return $this;
    }

    protected function replaceImgs(DomCrawler $dom): void
    {
        $dom->filter('picture')->each(
            function ($node) {
                $urlSrc = $node->filter('source')->attr('srcset') ?? $node->filter('source')->attr('data-srcset') ?? '';
                $imgUrls = array_map(fn ($item) => trim($item), explode(',', $urlSrc));
                $imgUrl = trim(explode(' ', $imgUrls[count($imgUrls) - 1])[0]);
                $imgUrl = str_replace(array('.jpg.webp', '.png.webp'), array('.jpg', '.png'), $imgUrl);

                $element = $node->getNode(0);

                $newElement = $element->ownerDocument->createElement('img');
                $newElement->setAttribute('src', $imgUrl);

                $element->parentNode->replaceChild($newElement, $element);
            }
        );

        $dom->filter('figure')->each(
            function ($node) {
                $imgNode = $node->filter('img');
                $imgUrl = $this->escUrl($imgNode->attr('src'));

                if ($imgNode->attr('data-src') !== null && is_url($imgNode->attr('data-src'))) {
                    $imgUrl = $imgNode->attr('data-src');
                }

                if ($imgNode->attr('data-lazy-src') !== null && is_url($imgNode->attr('data-lazy-src'))) {
                    $imgUrl = $imgNode->attr('data-lazy-src');
                }

                if (str_contains($imgUrl, '/proxy.php')) {
                    parse_str(parse_url($imgUrl, \PHP_URL_QUERY), $urlParse);
                    if ($img = Arr::get($urlParse, 'image')) {
                        $imgUrl = $img;
                    }
                }

                $element = $node->getNode(0);

                $newElement = $element->ownerDocument->createElement('img');
                $newElement->setAttribute('src', $imgUrl);

                $element->parentNode->replaceChild($newElement, $element);
            }
        );

        $dom->filter('img')->each(
            function ($node) {
                /** @var DomCrawler $node */
                $imgUrl = $this->escUrl($node->attr('src'));

                if ($node->attr('data-src') !== null && is_url($node->attr('data-src'))) {
                    $imgUrl = $node->attr('data-src');
                }

                if ($node->attr('data-lazy-src') !== null && is_url($node->attr('data-lazy-src'))) {
                    $imgUrl = $node->attr('data-lazy-src');
                }

                if (str_contains($imgUrl, '/proxy.php')) {
                    parse_str(parse_url($imgUrl, \PHP_URL_QUERY), $urlParse);
                    if ($img = Arr::get($urlParse, 'image')) {
                        $imgUrl = $img;
                    }
                }

                /** @var \DOMNode $element */
                $element = $node->getNode(0);

                $newElement = $element->ownerDocument->createElement('img');
                $newElement->setAttribute('src', $imgUrl);

                $element->parentNode->replaceChild($newElement, $element);
            }
        );

        $dom->filter('iframe')->each(
            function ($node) {
                /** @var DomCrawler $node */
                $imgUrl = $this->escUrl($node->attr('src'));

                if ($node->attr('data-src') !== null && is_url($node->attr('data-src'))) {
                    $imgUrl = $node->attr('data-src');
                }

                if ($node->attr('data-lazy-src') !== null && is_url($node->attr('data-lazy-src'))) {
                    $imgUrl = $node->attr('data-lazy-src');
                }

                /** @var \DOMNode $element */
                $element = $node->getNode(0);
                $newElement = $element->ownerDocument->createElement('iframe');
                $newElement->setAttribute('src', $imgUrl);
                $newElement->setAttribute('frameborder', '0');
                $newElement->setAttribute('allowfullscreen', '1');
                $element->parentNode->replaceChild($newElement, $element);
            }
        );

        $dom->filter('source[type=image\/webp]')->each(
            function ($node) {
                if ($node->attr('data-pin-media') !== null) {
                    $imgUrl = $this->escUrl($node->attr('data-pin-media'));
                    $element = $node->getNode(0);

                    $newElement = $element->ownerDocument->createElement('img');
                    $newElement->setAttribute('src', $imgUrl);

                    $element->parentNode->replaceChild($newElement, $element);
                }
            }
        );
    }

    protected function escUrl(?string $url): null|string
    {
        $url = trim($url);

        return str_replace(["\n", "\t", " "], '', $url);
    }
}
