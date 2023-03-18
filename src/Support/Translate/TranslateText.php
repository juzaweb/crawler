<?php

namespace Juzaweb\Crawler\Helpers\Translate;

use Illuminate\Support\Facades\Log;
use Juzaweb\CMS\Support\HtmlDom;

class TranslateText
{
    protected string $source;
    protected string $target;
    protected string $text;
    protected string $preview;
    protected array $noneReplace = [];

    public function __construct($source, $target, $text, $preview = false)
    {
        $this->source = $source;
        $this->target = $target;
        $this->text = $text;
        $this->preview = $preview;
    }

    public function translateBBCode(): array|bool|string|null
    {
        $this->noneReplace();
        $trans_text = $this->text;
        $texts = preg_split('|[[\/\!]*?[^\[\]]*?]|si', $trans_text, -1, PREG_SPLIT_NO_EMPTY);
        $translate = new GoogleTranslate();

        foreach ($texts as $textrow) {
            if ($this->excludeTranslate($textrow)) {
                continue;
            }

            if ($this->excludeTranslate($textrow)) {
                continue;
            }

            $raw = [" ", "\n", "\t", "\r"];

            $before = '';
            $after = '';

            if (in_array($textrow[0], $raw)) {
                $before = $textrow[0];
            }

            if (in_array($textrow[-1], $raw)) {
                $after = $textrow[-1];
            }

            try {
                $cv_text = html_entity_decode(trim($textrow), ENT_QUOTES, 'UTF-8');
                $trans = $translate->translate($this->source, $this->target, $cv_text);
                if ($trans === false) {
                    return false;
                }
            } catch (\Exception $exception) {
                if ($this->preview) {
                    dd(trim($textrow), $exception->getMessage());
                }

                Log::error(json_encode([trim($textrow), $exception->getMessage()]));
                return false;
            }

            $trans_text = preg_replace(
                '/' . preg_quote($textrow, '/') . '/',
                $before . $trans . $after,
                $trans_text,
                1
            );

            if (!$this->preview) {
                sleep(3);
            }
        }

        $this->text = $trans_text;
        $this->parseNoneReplace();
        return $this->text;
    }

    protected function dom(): bool|HtmlDom
    {
        return str_get_html($this->text);
    }

    protected function noneReplace(): void
    {
        foreach ($this->dom()->find('pre') as $index => $e) {
            $this->text = str_replace(
                $e->outertext,
                '[nonepeplace' . $index . '][/nonepeplace' . $index . ']',
                $this->text
            );
            $this->noneReplace[$index] = $e->innertext;
        }
    }

    protected function parseNoneReplace(): void
    {
        foreach ($this->noneReplace as $index => $item) {
            $this->text = str_replace(
                '[nonepeplace' . $index . '][/nonepeplace' . $index . ']',
                '<pre>' . $item . '</pre>',
                $this->text
            );
        }

        $this->text = str_replace("<pre><code>", "<pre>", $this->text);
        $this->text = str_replace("</code></pre>", "</pre>", $this->text);
    }

    protected function excludeTranslate($text): bool
    {
        if (empty(trim($text))) {
            return true;
        }

        if ($text == " ") {
            return true;
        }

        if (is_url($text) || is_image_path($text)) {
            return true;
        }

        return false;
    }
}
