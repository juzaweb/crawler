<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
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
            $this->noneReplace[$key] = ['text' => $e->text(), 'lang' => $this->detachLangTagCode($e)];
        }

        $dom = $this->dom($text);
        foreach ($dom->find('pre') as $e) {
            $key = $this->generateNoneReplaceKey();
            $text = str_replace($e->outertext, '[none_replace-'. $key .'][/none_replace-'. $key .']', $text);
            $this->noneReplace[$key] = ['text' => $e->text(), 'lang' => $this->detachLangTagCode($e)];
        }

        return $text;
    }

    protected function parseNoneReplace($text): null|string
    {
        foreach ($this->noneReplace as $index => $item) {
            $replace = $item['lang']
                ? '[code lang='. $item['lang'] .']' . $this->parseCodeText($item['text']) . '[/code]'
                : '[code]' . $this->parseCodeText($item['text']) . '[/code]';
            $text = str_replace('[none_replace-'. $index .'][/none_replace-'. $index .']', $replace, $text);
        }

        return $text;
    }

    protected function parseCodeText(string $text): string
    {
        return html_entity_decode(
            strip_tags(str_replace(["<br>", "<br/>", "<br />"], "\n", $text)),
            ENT_COMPAT
        );
    }

    protected function detachLangTagCode($e): ?string
    {
        if ($e->hasAttribute('data-lang')) {
            return $e->{'data-lang'};
        }

        $classes = explode(' ', $e->getAttribute('class') ?? '');
        $class = array_filter($classes, fn($class) => str_contains($class, 'language-'));
        if ($class) {
            $lang = explode('-', $class[0]);
            if (!empty($lang[1])) {
                return $lang[1];
            }
        }

        // CodeMirror
        if ($setting = $e->getAttribute('data-setting')) {
            if (is_json($setting)) {
                $setting = json_decode($setting, true);
                if (!empty($setting['language'])) {
                    return Str::lower($setting['language']);
                }
            }
        }

        return null;
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
}
