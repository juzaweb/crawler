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

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Juzaweb\Backend\Models\Post;
use Juzaweb\Backend\Models\Resource;
use Juzaweb\CMS\Contracts\PostImporterContract;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface as CrawlerTemplate;
use Juzaweb\Crawler\Interfaces\TemplateHasResource;
use Juzaweb\Crawler\Models\CrawlerContent;
use Juzaweb\Crawler\Models\CrawlerLink;
use Juzaweb\Crawler\Models\CrawlerPage;
use Juzaweb\Crawler\Support\Crawlers\ContentCrawler;
use Juzaweb\Crawler\Support\Crawlers\LinkCrawler;

class Crawler implements CrawlerContract
{
    protected PostImporterContract $postImporter;

    public function __construct(PostImporterContract $postImporter)
    {
        $this->postImporter = $postImporter;
    }

    public function crawPageLinks(CrawlerPage $page): bool|int
    {
        $template = $page->website->getTemplateClass();

        $crawUrl = $page->url;
        if ($page->next_page > 1 && $page->url_with_page) {
            $crawUrl = str_replace(
                ['{page}'],
                [$page->next_page],
                $page->url_with_page
            );
        }

        $items = $this->createLinkCrawler()->crawLinksUrl(
            $crawUrl,
            $template,
            (bool) $page->is_resource_page
        );

        $data = $this->checkAndInsertLinks($items, $page);

        return count($data);
    }

    public function crawLinksUrl(string $url, CrawlerTemplate $template): array
    {
        return $this->createLinkCrawler()->crawLinksUrl($url, $template);
    }

    public function crawContentLink(CrawlerLink $link): bool
    {
        $template = $link->website->getTemplateClass();

        $isResource = (bool) $link->page->is_resource_page;

        $data = $this->createContentCrawler()->crawContentsUrl(
            $link->url,
            $template,
            $isResource
        );

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

            if ($isResource) {
                $resource = $this->importResourceData($data, $link);

                $content->update(
                    [
                        'resource_id' => $resource[0]->id,
                        'status' => CrawlerContent::STATUS_DONE
                    ]
                );
            } else {
                $data['type'] = $link->page->post_type;

                $post = $this->importPostData($data, $link, $template);

                $content->update(
                    [
                        'post_id' => $post->id,
                        'status' => CrawlerContent::STATUS_DONE
                    ]
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return true;
    }

    protected function checkAndInsertLinks(array $items, CrawlerPage $page): array
    {
        $urls = CrawlerLink::whereIn('url', $items)
            ->get(['url_hash'])
            ->keyBy('url_hash')
            ->toArray();

        $data = collect($items)
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
            ->filter(
                function ($url) use ($urls) {
                    return is_url($url['url']) && !isset($urls[$url['url_hash']]);
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

        return $data;
    }

    protected function importResourceData(array $data, CrawlerLink $link): array
    {
        $resources = [];
        foreach ($data as $key => $item) {
            $item['type'] = $key;
            $item['post_id'] = $link->page->parent_post_id;

            $resource = Resource::create($item);

            if ($metas = Arr::get($item, 'meta')) {
                $resource->syncMetas($metas);
            }

            $resources[] = $resource;
        }

        return $resources;
    }

    protected function importPostData(array $data, CrawlerLink $link, CrawlerTemplate $template): Post
    {
        $post = $this->postImporter->import($data);

        if ($template instanceof TemplateHasResource) {
            $urlPage = $template->getResourceUrlWithPage() ? str_replace(
                ['{post_url}'],
                [$link->url],
                $template->getResourceUrlWithPage()
            ) : null;

            CrawlerPage::firstOrCreate(
                [
                    'url' => $link->url,
                    'url_hash' => sha1($link->url),
                    'url_with_page' => $urlPage,
                    'post_type' => $link->page->post_type,
                    'active' => 1,
                    'website_id' => $link->website->id,
                    'parent_post_id' => $post->id,
                    'is_resource_page' => 1,
                ]
            );
        }

        return $post;
    }

    private function createLinkCrawler(): LinkCrawler
    {
        return app(LinkCrawler::class);
    }

    private function createContentCrawler(): ContentCrawler
    {
        return app(ContentCrawler::class);
    }
}
