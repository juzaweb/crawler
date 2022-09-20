<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    MIT
 */

namespace Juzaweb\Crawler\Support;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerPage;
use Juzaweb\Crawler\Models\CrawlerLink;

class Crawler implements CrawlerContract
{
    public function crawPageLinks(CrawlerPage $page): bool
    {
        $template = $page->website->template->getTemplateClass();

        $contents = $this->getClient()->get($page->url)->getBody()->getContents();

        $html = str_get_html($contents);

        $urls = $html->find($template->getLinkElement());

        if (empty($urls)) {
            return false;
        }

        $items = [];
        foreach ($urls as $url) {
            $ourl = $url->getAttribute(
                $template->getLinkElementAttribute()
            );

            $ourl = trim(get_full_url($ourl, $url));

            $items[] = [
                'url' => $ourl,
                'url_hash' => hash($ourl, ''),
                'website_id' => $page->website->id,
                'page_id' => $page->id,
                'category_ids' => $page->category_ids,
            ];
        }

        $urls = CrawlerLink::whereIn('url', $items)
            ->pluck('url')
            ->toArray();

        $data = collect($items)
            ->filter(
                function ($url) use ($urls) {
                    return is_url($url) && !in_array($url, $urls);
                }
            )->toArray();

        DB::beginTransaction();
        try {
            DB::table(CrawlerLink::getTableName())->insert($data);

            $next_page = ($page->url_page && $page->next_page > 0)
                ? $page->next_page + 1
                : ($page->next_page > 0 ? 1 : 0);

            $page->update(
                [
                    'crawler_date' => now(),
                    'next_page' => $next_page,
                ]
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return true;
    }

    public function crawLinkContent(CrawlerLink $link): bool
    {
        //
    }

    protected function getClient(): Client
    {
        return new Client(['timeout' => 10]);
    }
}
