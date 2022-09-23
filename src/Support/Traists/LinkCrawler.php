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
use Juzaweb\Crawler\Support\Templates\CrawlerTemplate;
use Juzaweb\Crawler\Models\CrawlerLink;
use Juzaweb\Crawler\Models\CrawlerPage;

trait LinkCrawler
{
    public function crawPageLinks(CrawlerPage $page): bool
    {
        $template = $page->website->template->getTemplateClass();

        $items = $this->crawLinksUrl($page->url, $template);

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
                        'url_hash' => hash('sha1', $item),
                        'website_id' => $page->website->id,
                        'page_id' => $page->id,
                        'category_ids' => $page->category_ids,
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

        return true;
    }

    public function crawLinksUrl(string $url, CrawlerTemplate $template): array
    {
        $html = $this->createHTMLDomFromUrl($url);

        $urls = $html->find($template->getLinkElement());

        if (empty($urls)) {
            return [];
        }

        $items = [];
        foreach ($urls as $url) {
            $href = $url->getAttribute(
                $template->getLinkElementAttribute()
            );

            $items[] = trim(get_full_url($href, $url));
        }

        return $items;
    }
}
