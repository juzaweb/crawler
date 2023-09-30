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

use Illuminate\Support\Facades\Storage;

class BBCodeToHTML
{
    public static function toHTML(?string $text, ?string $alt = null): string
    {
        return app(static::class)->convert($text, $alt);
    }

    public function convert(?string $text, ?string $alt = ''): string|null
    {
        $text = $this->replaceTabs($text, $alt);
        $text = trim($text);
        $text = str_replace("\t", "", $text);
        $text = str_replace("\n", "<br>", $text);
        return str_replace("<br><br>", "<br>", $text);
    }

    protected function replaceTabs(?string $text, ?string $alt = ''): string|null
    {
        $imageUrl = Storage::disk('public')->url('/');

        $text = str_replace(
            [
                '[code][code]',
                '[/code][/code]'
            ],
            [
                '[code]',
                '[/code]'
            ],
            $text
        );

        $basicTags = ['p', 'b', 'i', 'u', 'h3', 'ul', 'ol', 'li'];
        $find = collect($basicTags)->map(fn ($item) => "[{$item}]")->toArray();
        $find = collect($basicTags)->map(fn ($item) => "[/{$item}]")->merge($find)->toArray();
        $replace = collect($basicTags)->map(fn ($item) => "<{$item}>")->toArray();
        $replace = collect($basicTags)->map(fn ($item) => "</{$item}>")->merge($replace)->toArray();

        $text = str_replace($find, $replace, $text);

        $find = [
            '~\[quote\](.*?)\[/quote\]~s',
            '~\[size=(.*?)\](.*?)\[/size\]~s',
            '~\[color=(.*?)\](.*?)\[/color\]~s',
            '~\[url\](.*?)\[/url\]~s',
            '~\[url=(.*?)\](.*?)\[/url\]~s',
            '~\[img\]((https?)://.*?)\[/img\]~s',
            '~\[img=([0-9]+)\]((https?)://.*?)\[/img\]~s',
            '~\[img\](data:.*?)\[/img\]~s',
            '~\[img\](.*?)\[/img\]~s',
            '~\[img=([0-9]+)\](.*?)\[/img\]~s',
            '~\[embed\](.*?)\[/embed\]~s',
            '~\[code_inline\](.*?)\[/code_inline\]~s',
            '~\[code_inline lang=([a-zA-Z0-9]+)\](.*?)\[/code_inline\]~s',
            '~\[code lang=([a-zA-Z0-9]+)\](.*?)\[/code\]~s',
            '~\[code\](.*?)\[/code\]~s',
            '~\[table\](.*?)\[/table\]~s',
            '~\[thead\](.*?)\[/thead\]~s',
            '~\[tbody\](.*?)\[/tbody\]~s',
            '~\[tfoot\](.*?)\[/tfoot\]~s',
            '~\[caption\](.*?)\[/caption\]~s',
            '~\[tr\](.*?)\[/tr\]~s',
            '~\[td\](.*?)\[/td\]~s',
            '~\[QUOTE\](.*?)\[/QUOTE\]~s',
        ];

        $replace = [
            '<pre><code>$1</code></pre>',
            '<span style="font-size:$1px;">$2</span>',
            '<span style="color:$1;">$2</span>',
            '<a href="$1" rel="nofollow" target="_blank">$1</a>',
            '<a href="$1" rel="nofollow" target="_blank">$2</a>',
            '<img src="$1" alt="'. e($alt) .'" />',
            '<img width="$1" src="$2" alt="'. e($alt) .'" />',
            '<img src="$1" alt="'. e($alt) .'" />',
            '<img src="'. $imageUrl .'$1" alt="'. e($alt) .'" />',
            '<img width="$1" src="'. $imageUrl .'$2" alt="'. e($alt) .'" />',
            '<div class="embed-responsive">'.
            '<iframe src="$1" class="embed-responsive-item" allowfullscreen></iframe>'
            .'</div>',
            '<code>$1</code>',
            '<code class="language-$1">$2</code>',
            '<pre><code class="language-$1">$2</code></pre>',
            '<pre><code>$1</code></pre>',
            '<table>$1</table>',
            '<thead>$1</thead>',
            '<tbody>$1</tbody>',
            '<tfoot>$1</tfoot>',
            '<caption>$1</caption>',
            '<tr>$1</tr>',
            '<td>$1</td>',
            '<blockquote>$1</blockquote>',
        ];

        return preg_replace($find, $replace, $text);
    }
}
