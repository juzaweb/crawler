<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    MIT
 */

namespace Juzaweb\Crawler\Support\Crawlers;

use Illuminate\Support\Facades\DB;
use Juzaweb\Crawler\Abstracts\CrawlerAbstract;
use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface as CrawlerTemplate;
use Juzaweb\Crawler\Models\CrawlerLink;
use Juzaweb\Crawler\Models\CrawlerPage;

class LinkCrawler extends CrawlerAbstract
{
    public function crawPageLinks(CrawlerPage $page): bool|int
    {
        $template = $page->website->getTemplateClass();

        $crawUrl = $page->url;
        if ($page->next_page > 1 && $page->url_with_page) {
            $crawUrl = str_replace(['{page}'], [$page->next_page], $page->url_with_page);
        }

        $items = $this->crawLinksUrl($crawUrl, $template);

        $urls = CrawlerLink::whereIn('url', $items)
            ->pluck('url')
            ->toArray();

        $data = collect($items)
            ->filter(
                function ($url) use ($urls) {
                    return is_url($url) && !in_array($url, $urls);
                }
            )
            ->map(
                function ($item) use ($page) {
                    return [
                        'url' => $item,
                        'url_hash' => sha1($item),
                        'website_id' => $page->website->id,
                        'page_id' => $page->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                }
            )
            ->toArray();

        DB::beginTransaction();
        try {
            DB::table(CrawlerLink::getTableName())->insert($data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return count($data);
    }

    public function crawLinksUrl(string $url, CrawlerTemplate $template): array
    {
        return $this->crawLinkViaElement(
            $url,
            $template->getLinkElement(),
            $template->getLinkElementAttribute()
        );
    }

    public function crawLinkViaElement(
        string $url,
        string $element,
        string $elementAttribute = 'href'
    ): array {
        $html = $this->createHTMLDomFromUrl($url);

        $urls = $html->find($element);

        if (empty($urls)) {
            return [];
        }

        $items = [];
        foreach ($urls as $url) {
            $href = $url->getAttribute($elementAttribute);

            $items[] = trim(get_full_url($href, $url));
        }

        return $items;
    }
}
