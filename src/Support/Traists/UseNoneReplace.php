<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/juzacms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Support\Traists;

use Illuminate\Support\Str;
use Juzaweb\CMS\Support\HtmlDom;

trait UseNoneReplace
{
    protected function noneReplace($text)
    {
        if (!$dom = $this->dom($text)) {
            return $text;
        }

        foreach ($dom->find('pre code') as $e) {
            $key = $this->generateNoneReplaceKey();
            $text = str_replace($e->parent()->outertext, '[none_replace-'. $key .'][/none_replace-'. $key .']', $text);
            $this->noneReplace[$key] = [
                'text' => $e->innertext(),
                'lang' => $this->detachLangTagCode($e),
                'target' => 'code',
            ];
        }

        $dom = $this->dom($text);
        foreach ($dom->find('pre') as $e) {
            $key = $this->generateNoneReplaceKey();
            $text = str_replace($e->outertext, '[none_replace-'. $key .'][/none_replace-'. $key .']', $text);
            $this->noneReplace[$key] = [
                'text' => $e->innertext(),
                'lang' => $this->detachLangTagCode($e),
                'target' => 'code',
            ];
        }

        $dom = $this->dom($text);
        foreach ($dom->find('code') as $e) {
            $key = $this->generateNoneReplaceKey();
            $text = str_replace($e->outertext, '[none_replace-'. $key .'][/none_replace-'. $key .']', $text);
            $this->noneReplace[$key] = [
                'text' => $e->innertext(),
                'lang' => $this->detachLangTagCode($e),
                'target' => 'code_inline',
            ];
        }

        return $text;
    }

    protected function parseNoneReplace($text): null|string
    {
        foreach ($this->noneReplace as $index => $item) {
            $replace = $item['lang']
                ? "[{$item['target']} lang={$item['lang']}]". $this->parseCodeText($item['text'])."[/{$item['target']}]"
                : "[{$item['target']}]" . $this->parseCodeText($item['text']) . "[/{$item['target']}]";
            $text = str_replace('[none_replace-'. $index .'][/none_replace-'. $index .']', $replace, $text);
        }

        return $text;
    }

    protected function parseNoneReplaceHtml($text): null|string
    {
        foreach ($this->noneReplace as $index => $item) {
            $replace = $item['text'];
            $text = str_replace('[none_replace-'. $index .'][/none_replace-'. $index .']', $replace, $text);
        }

        return $text;
    }

    protected function parseCodeText(string $text): string
    {
        if ($this->isTextHtml($text)) {
            $text = str_replace(["<br>", "<br/>", "<br />"], "\n", $text);
            $text = str_replace(["</div>", "</p>"], ["</div>\n", "</p>\n"], $text);
            return strip_tags($text);
        }

        return $text;
    }

    protected function detachLangTagCode($e): ?string
    {
        if ($e->hasAttribute('data-lang')) {
            return $e->{'data-lang'};
        }

        $classes = explode(' ', $e->getAttribute('class') ?? '');
        $class = array_filter($classes, fn($class) => str_contains($class, 'language-'));
        if ($class) {
            if (isset($class[0])) {
                $lang = explode('-', $class[0]);
                if (!empty($lang[1])) {
                    return $this->parseLangCodeToClass($lang[1]);
                }
            }
        }

        // CodeMirror
        if ($setting = $e->getAttribute('data-setting')) {
            if (is_json($setting)) {
                $setting = json_decode($setting, true);
                if (!empty($setting['language'])) {
                    return $this->parseLangCodeToClass(Str::lower($setting['language']));
                }
            }
        }

        return null;
    }

    protected function parseLangCodeToClass(string $lang): string
    {
        switch ($lang) {
            case 'blade':
                $lang = 'html';
                break;
            case 'typescript':
                $lang = 'javascript';
                break;
        }

        return $lang;
    }

    protected function generateNoneReplaceKey(): string
    {
        do {
            $str = Str::uuid()->toString();
        } while (isset($this->noneReplace[$str]));

        return $str;
    }

    protected function dom($text): bool|HtmlDom
    {
        return str_get_html($text);
    }

    protected function isTextHtml(string $text): bool
    {
        return $text != strip_tags($text);
    }
}
