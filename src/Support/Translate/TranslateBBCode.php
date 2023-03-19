<?php

namespace Juzaweb\Crawler\Support\Translate;

use Illuminate\Support\Facades\Log;
use Juzaweb\CMS\Contracts\GoogleTranslate;
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

    public function __construct($source, $target, $text, $preview = false)
    {
        $this->source = $source;
        $this->target = $target;
        $this->text = $text;
        $this->preview = $preview;
    }

    public function translate(): string|null
    {
        $transText = HTMLToBBCode::toBBCode($this->noneReplace($this->text));
        $texts = preg_split('|[[\/\!]*?[^\[\]]*?]|si', $transText, -1, PREG_SPLIT_NO_EMPTY);
        $translate = app(GoogleTranslate::class);

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

            $transText = preg_replace(
                '/' . preg_quote($textrow, '/') . '/',
                $before . $trans . $after,
                $transText,
                1
            );

            if (!$this->preview) {
                sleep(3);
            }

            sleep(2);
        }

        $this->text = BBCodeToHTML::toHTML($this->parseNoneReplace($transText));
        return $this->text;
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
