<?php

namespace Juzaweb\Crawler\Support\Translate;

use Juzaweb\CMS\Contracts\GoogleTranslate;
use Juzaweb\Crawler\Exceptions\TranslateBBCodeException;
use Juzaweb\Crawler\Support\Converter\BBCodeToHTML;
use Juzaweb\Crawler\Support\Converter\HTMLToBBCode;
use Juzaweb\Crawler\Support\Traists\UseNoneReplace;

class TranslateBBCode
{
    use UseNoneReplace;

    protected string $source;
    protected string $target;
    protected string $text;
    protected string $preview;
    protected array $noneReplace = [];
    protected string|array|null $proxy = null;

    public function __construct($source, $target, $text, $preview = false)
    {
        $this->source = $source;
        $this->target = $target;
        $this->text = $text;
        $this->preview = $preview;
    }

    public function withProxy(string|array $proxy): static
    {
        $this->proxy = $proxy;

        return $this;
    }

    public function translate(): string|null
    {
        $transText = HTMLToBBCode::toBBCode($this->noneReplace($this->text));
        $texts = preg_split('|[[\/\!]*?[^\[\]]*?]|si', $transText, -1, PREG_SPLIT_NO_EMPTY);
        $translate = app(GoogleTranslate::class);
        if ($this->proxy) {
            $translate = $translate->withProxy($this->proxy);
        }

        foreach ($texts as $textrow) {
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
                $cvText = html_entity_decode(trim($textrow), ENT_QUOTES, 'UTF-8');
                $trans = $translate->translate($this->source, $this->target, $cvText);
            } catch (\Exception $exception) {
                if ($this->preview) {
                    dd(trim($textrow), $exception->getMessage());
                }
                throw new TranslateBBCodeException($exception->getMessage());
            }

            $find = '/\[.*?\](*SKIP)(*FAIL)|'. preg_quote($textrow, '/') .'/';

            $transText = preg_replace_callback(
                $find,
                function ($matches) use ($trans, $before, $after) {
                    if (ctype_upper($matches[0])){
                        $trans = mb_strtoupper($trans);
                    } else if (ctype_upper(mb_substr($matches[0], 0, 1))){
                        $trans = ucfirst($trans);
                    }

                    return $before . $trans . $after;
                },
                $transText,
                1
            );

            if (!$this->preview) {
                sleep(3);
            }

            sleep(2);
        }

        $transText = BBCodeToHTML::toHTML($transText);
        $this->text = $this->replaceCodeText($this->parseNoneReplace($transText));
        return $this->text;
    }

    protected function replaceCodeText(string $text): string
    {
        $find = [
            '~\[code_inline\](.*?)\[/code_inline\]~s',
            '~\[code_inline lang=([a-zA-Z0-9]+)\](.*?)\[/code_inline\]~s',
            '~\[code lang=([a-zA-Z0-9]+)\](.*?)\[/code\]~s',
            '~\[code\](.*?)\[/code\]~s',
        ];

        $replace = [
            '<code>$1</code>',
            '<code class="language-$1">$2</code>',
            '<pre><code class="language-$1">$2</code></pre>',
            '<pre><code>$1</code></pre>',
        ];

        return preg_replace($find, $replace, $text);
    }

    protected function excludeTranslate($text): bool
    {
        if (empty(trim($text))) {
            return true;
        }

        if ($text == " ") {
            return true;
        }

        if (is_url($text)) {
            return true;
        }

        return false;
    }
}
