<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    MIT
 */

namespace Juzaweb\Crawler\Support\Traists;

use Illuminate\Support\Facades\DB;
use Juzaweb\Crawler\Models\CrawlerContent;
use Juzaweb\Crawler\Models\CrawlerLink;
use Juzaweb\Crawler\Support\CrawlerElement;
use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface as CrawlerTemplate;

trait ContentCrawler
{
    public function crawContentLink(CrawlerLink $link): bool
    {
        $template = $link->website->getTemplateClass();

        $data = $this->crawContentsUrl($link->url, $template);

        DB::beginTransaction();
        try {
            $content = CrawlerContent::updateOrCreate(
                [
                    'link_id' => $link->id
                ],
                [
                    'components' => $data,
                    'link_id' => $link->id,
                    'page_id' => $link->page_id,
                    'status' => CrawlerContent::STATUS_PENDING,
                ]
            );

            $data['type'] = $link->page->post_type;
            //$data['status'] = $link->page->post_type;

            $post = $this->postImport->import($data);

            $content->update(
                [
                    'post_id' => $post->id,
                    'status' => CrawlerContent::STATUS_DONE
                ]
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return true;
    }

    public function crawContentsUrl(string $url, CrawlerTemplate $template): array
    {
        $contents = $this->createHTMLDomFromUrl($url);

        $contents->removeScript();

        //$contents->removeInternalLink(get_domain_by_url($url));

        $result = [];
        foreach ($template->getDataElements() as $code => $el) {
            $element = new CrawlerElement($el);
            $result[$code] = $element->getValue($contents);
        }

        return $result;
    }
}
