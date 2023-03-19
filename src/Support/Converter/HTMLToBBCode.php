<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     The Anh Dang <dangtheanh16@gmail.com>
 * @link       https://juzaweb.com/cms
 * @license    MIT
 */

namespace Juzaweb\Crawler\Support\Converter;

use Juzaweb\Crawler\Support\Traists\UseNoneReplace;

class HTMLToBBCode
{
    use UseNoneReplace;

    protected array $noneReplace = [];

    public static function toBBCode(?string $text): null|string
    {
        return app(static::class)->convert($text);
    }

    public function convert(?string $text): null|string
    {
        $text = $this->noneReplace($text);
        $text = $this->replaceTabs($text);
        $text = $this->replaceLinks($text);
        $text = $this->replaceImgs($text);
        $text = $this->replaceIframe($text);
        $text = str_replace("\t", "", $text);
        $text = str_replace(["<br>", "<br/>", "<br />"], "\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = $this->parseNoneReplace($text);
        return trim($text);
    }

    protected function replaceLinks($text): array|string
    {
        if (!$dom = $this->dom($text)) {
            return $text;
        }

        foreach ($dom->find('a') as $e) {
            $text = str_replace(
                $e->outertext,
                '[url=' . $this->escUrl($e->href) . ']'. $e->innertext .'[/url]',
                $text
            );
        }

        return $text;
    }

    protected function replaceImgs($text)
    {
        if (!$dom = $this->dom($text)) {
            return $text;
        }

        foreach ($dom->find('picture') as $e) {
            $imgUrls = explode(',', $e->find('source', 0)->srcset ?? '');
            $imgUrl = trim(explode(' ', $imgUrls[0])[0]);

            if ($imgUrl) {
                $text = str_replace(
                    $e->outertext,
                    '[img]'. $imgUrl .'[/img]',
                    $text
                );
            } else {
                $text = str_replace(
                    $e->outertext,
                    '',
                    $text
                );
            }
        }

        foreach ($dom->find('img') as $e) {
            $imgUrl = $this->escUrl($e->src);

            if (isset($e->{'data-src'}) && is_url($e->{'data-src'})) {
                $imgUrl = $e->{'data-src'};
            }

            if (isset($e->{'data-lazy-src'}) && is_url($e->{'data-lazy-src'})) {
                $imgUrl = $e->{'data-lazy-src'};
            }

            $text = str_replace(
                $e->outertext,
                '[img]'. $imgUrl .'[/img]',
                $text
            );
        }

        foreach ($dom->find('source[type=image/webp]') as $e) {
            if (isset($e->{'data-pin-media'})) {
                $text = str_replace(
                    $e->outertext,
                    '[img]'. $this->escUrl($e->{'data-pin-media'}) .'[/img]',
                    $text
                );
            }
        }

        return $text;
    }

    protected function replaceIframe(?string $text): null|string
    {
        if (!$dom = $this->dom($text)) {
            return $text;
        }

        foreach ($dom->find('iframe') as $e) {
            if ($e->src) {
                $text = str_replace(
                    $e->outertext,
                    '[embed]'. $this->escUrl($e->src) .'[/embed]',
                    $text
                );
            }

            if (@$e->{'data-src'}) {
                $text = str_replace(
                    $e->outertext,
                    '[embed]'. $this->escUrl($e->{'data-src'}) .'[/embed]',
                    $text
                );
            }

            if (@$e->{'data-lazy-src'}) {
                $text = str_replace(
                    $e->outertext,
                    '[embed]'. $this->escUrl($e->{'data-lazy-src'}) .'[/embed]',
                    $text
                );
            }
        }

        return $text;
    }

    protected function replaceFonts(?string $text): null|string
    {
        if (!$dom = $this->dom($text)) {
            return $text;
        }

        foreach ($dom->find('span') as $e) {
            if (!isset($e->style)) {
                continue;
            }

            $text = str_replace(
                $e->outertext,
                '[size=]'. trim($e->innertext) .'[/size]',
                $text
            );
        }

        return $text;
    }

    protected function replaceTabs($text): null|string
    {
        if (!$dom = $this->dom($text)) {
            return $text;
        }

        foreach ($dom->find('p') as $e) {
            $text = str_replace($e->outertext, '[p]'. trim($e->innertext) .'[/p]', $text);
        }

        foreach ($dom->find('b') as $e) {
            $text = str_replace($e->outertext, '[b]'. trim($e->innertext) .'[/b]', $text);
        }

        foreach ($dom->find('u') as $e) {
            $text = str_replace($e->outertext, '[u]'. trim($e->innertext) .'[/u]', $text);
        }

        foreach ($dom->find('i') as $e) {
            $text = str_replace($e->outertext, '[i]'. trim($e->innertext) .'[/i]', $text);
        }

        foreach ($dom->find('ul') as $e) {
            $text = str_replace($e->outertext, '[ul]'. trim($e->innertext) .'[/ul]', $text);
        }

        foreach ($dom->find('ol') as $e) {
            $text = str_replace($e->outertext, '[ol]'. trim($e->innertext) .'[/ol]', $text);
        }

        foreach ($dom->find('li') as $e) {
            $text = str_replace($e->outertext, '[li]'. trim($e->innertext) .'[/li]', $text);
        }

        foreach ($dom->find('strong') as $e) {
            $text = str_replace($e->outertext, '[b]'. trim($e->innertext) .'[/b]', $text);
        }

        for ($i=1; $i<=6; $i++) {
            foreach ($dom->find('h' . $i) as $e) {
                $text = str_replace($e->outertext, '[h3]'. trim($e->innertext) .'[/h3]', $text);
            }
        }

        return $text;
    }

    protected function escUrl(?string $url): null|string
    {
        $url = trim($url);
        return str_replace(["\n", "\t", " "], '', $url);
    }
}
