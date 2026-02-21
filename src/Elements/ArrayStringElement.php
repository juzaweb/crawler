<?php
/**
 * LARABIZ CMS - Full SPA Laravel CMS
 *
 * @package    larabizcms/larabiz
 * @author     The Anh Dang
 * @link       https://larabiz.com
 */

namespace Juzaweb\Modules\Crawler\Elements;

use Juzaweb\Modules\Crawler\Contracts\Element;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class ArrayStringElement extends BaseElement implements Element
{
    public string $format = 'text';

    public function format(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function formatHtml(): static
    {
        return $this->format('html');
    }

    public function getValueFrom(string $html): array
    {
        $crawler = new DomCrawler($html);

        return $crawler->filter($this->element)->each(
            function (DomCrawler $node) {
                if ($this->attribute) {
                    return $node->attr($this->attribute);
                }

                if ($this->format == 'html') {
                    return trim($this->replateHtmlCharacters($node->html()));
                }

                return trim($node->text());
            }
        );
    }
}
