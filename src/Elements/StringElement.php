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

class StringElement extends BaseElement implements Element
{
    public function getValueFrom(string $html): ?string
    {
        $crawler = new DomCrawler($html);

        $this->handleRemoveElements($crawler);

        if ($this->attribute) {
            try {
                $text = $crawler->filter($this->element)->eq($this->index)->attr($this->attribute);
            } catch (\InvalidArgumentException $e) {
                return null;
            }
        } else {
            $text = $crawler->filter($this->element)->eq($this->index)->text();
        }

        $text = str_replace(['&nbsp;', '&nbsp'], ' ', $text);

        return trim(remove_zero_width_space_string($text));
    }
}
