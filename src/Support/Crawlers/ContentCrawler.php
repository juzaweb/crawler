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

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Juzaweb\Backend\Models\Post;
use Juzaweb\Backend\Models\Resource;
use Juzaweb\CMS\Contracts\PostImporterContract;
use Juzaweb\Crawler\Abstracts\CrawlerAbstract;
use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface as CrawlerTemplate;
use Juzaweb\Crawler\Interfaces\TemplateHasResource;
use Juzaweb\Crawler\Models\CrawlerContent;
use Juzaweb\Crawler\Models\CrawlerLink;
use Juzaweb\Crawler\Models\CrawlerPage;
use Juzaweb\Crawler\Support\CrawlerElement;

class ContentCrawler extends CrawlerAbstract
{
    protected PostImporterContract $postImport;

    public function __construct(PostImporterContract $postImport)
    {
        $this->postImport = $postImport;
    }

    public function crawContentLink(CrawlerLink $link): bool
    {
        $template = $link->website->getTemplateClass();

        $data = $this->crawContentsUrl($link, $template);

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

            if ($link->page->is_resource_page) {
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

    public function crawContentsUrl(CrawlerLink $link, CrawlerTemplate $template): array
    {
        $contents = $this->createHTMLDomFromUrl($link->url);

        $contents->removeScript();

        $result = [];
        if ($link->page->is_resource_page) {
            $elementData = $template->getDataResourceElements();

            foreach ($elementData as $key => $resource) {
                foreach ($resource['data'] ?? [] as $code => $el) {
                    $element = new CrawlerElement($el);
                    Arr::set($result, "$key.{$code}", $element->getValue($contents));
                }
            }
        } else {
            $elementData = $template->getDataElements();

            foreach ($elementData['data'] ?? [] as $code => $el) {
                $element = new CrawlerElement($el);
                Arr::set($result, $code, $element->getValue($contents));
            }
        }

        return $result;
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
        $post = $this->postImport->import($data);

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
}
