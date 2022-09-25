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
                        'website_id' => $link->website->id
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

    public function crawContentsUrl(string $url, CrawlerTemplate $template): array
    {
        $contents = $this->createHTMLDomFromUrl($url);

        $contents->removeScript();

        $elementData = $template->getDataElements();

        $result = [];

        foreach ($elementData['data'] ?? [] as $code => $el) {
            $element = new CrawlerElement($el);
            $result[$code] = $element->getValue($contents);
        }

        return $result;
    }

    public function crawContentsViaHTMLDom(string $url, $contents)
    {
        //
    }
}
