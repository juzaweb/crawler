<?php

use Juzaweb\Modules\Core\FileManager\MediaUploader;
use Symfony\Component\DomCrawler\Crawler;

if (! function_exists('replace_media_in_content')) {
    function replace_media_in_content(string $content): string
    {
        $crawler = new Crawler($content);

        $images = [];
        $crawler->filter('img')->each(
            function ($node) use (&$images) {
                /** @var Crawler $node */
                $src = $node->attr('src');

                if (!is_url($src)) {
                    return;
                }

                $images[] = $src;
            }
        );

        $images = array_unique($images);
        $newImages = [];
        foreach ($images as $image) {
            try {
                $upload = MediaUploader::make(str_replace(' .webp', '.webp', $image))->upload();
                $newImages[] = $upload->getUrl();
            } catch (Throwable $e) {
                continue;
            }
        }

        return str_replace($images, $newImages, $content);
    }
}

function reformat_html(string $html): string
{
    libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");

    $wrappedHtml = "<div id='wrapper-html-jw-wrapped'>{$html}</div>";

    // Load and auto-fix HTML
    $dom->loadHTML($wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    /** @var DOMElement $body */
    $body = $dom->getElementById('wrapper-html-jw-wrapped');
    $output = '';
    foreach ($body->childNodes as $child) {
        $output .= $dom->saveHTML($child);
    }

    return $output;
}

function fix_html(string $html): string
{
    $html = str_replace(['&nbsp;', '&nbsp'], ' ', $html);

    $html = remove_zero_width_space_string($html);

    $html = reformat_html($html);

    return str_replace(['<body>', '</body>'], '', $html);
}
