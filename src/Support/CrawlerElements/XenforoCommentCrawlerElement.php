<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Support\CrawlerElements;

use Juzaweb\Crawler\Support\Converter\BBCodeToHTML;
use Juzaweb\Crawler\Support\Converter\HTMLToBBCode;
use Juzaweb\Crawler\Support\CrawlerElement;
use Juzaweb\Crawler\Support\HtmlDomCrawler;

class XenforoCommentCrawlerElement extends CrawlerElement
{
    public function getValue(HtmlDomCrawler $domCrawler): null|array|string
    {
        if ($this->removes) {
            $this->removeElements($domCrawler);
        }

        $elements = $domCrawler->find('.message--post');

        if (empty($elements)) {
            return null;
        }

        $result = [];
        foreach ($elements as $index => $item) {
            if (in_array($index, $this->skipIndexs)) {
                continue;
            }

            $contentNode = $item->find('.message-body .bbWrapper', 0);
            $username = $item->find('.username', 0)?->text();

            $text = HTMLToBBCode::toBBCode($this->getHtmlNodeValue($contentNode));
            if (empty($text)) {
                continue;
            }

            $result[] = [
                'content' => BBCodeToHTML::toHTML($text),
                'author' => trim($username),
            ];
        }

        return $result;
    }
}
