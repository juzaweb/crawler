<?php
/**
 * LARABIZ CMS - Full SPA Laravel CMS
 *
 * @package    larabizcms/larabiz
 * @author     The Anh Dang
 * @link       https://larabiz.com
 */

namespace JuzawebModulesCrawler\Elements;

use JuzawebModulesCrawler\Contracts\Element;
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

        $text = str_replace('&nbsp;', ' ', $text);

        $text = str_replace('&nbsp', ' ', $text);

        return remove_zero_width_space_string($text);
    }
}
