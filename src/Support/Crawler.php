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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Juzaweb\Backend\Models\Post;
use Juzaweb\Backend\Models\Resource;
use Juzaweb\CMS\Contracts\HookActionContract;
use Juzaweb\CMS\Contracts\PostImporterContract;
use Juzaweb\CMS\Models\User;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Events\PostSuccess;
use Juzaweb\Crawler\Exceptions\CrawContentLinkException;
use Juzaweb\Crawler\Exceptions\CrawlerException;
use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface as CrawlerTemplate;
use Juzaweb\Crawler\Interfaces\TemplateHasResource;
use Juzaweb\Crawler\Jobs\AddCommentToPostJob;
use Juzaweb\Crawler\Jobs\Bus\ContentCrawlerJob;
use Juzaweb\Crawler\Models\CrawlerContent;
use Juzaweb\Crawler\Models\CrawlerLink;
use Juzaweb\Crawler\Models\CrawlerPage;
use Juzaweb\Crawler\Support\Crawlers\ContentCrawler;
use Juzaweb\Crawler\Support\Crawlers\LinkCrawler;
use Juzaweb\CrawlerTranslate\Support\Translate\CrawlerContentTranslation;

class Crawler implements CrawlerContract
{
    protected PostImporterContract $postImporter;

    protected HookActionContract $hookAction;

    public function __construct($app)
    {
        $this->postImporter = $app[PostImporterContract::class];
        $this->hookAction = $app[HookActionContract::class];
    }

    public function crawPageLinks(CrawlerPage $page, int $pageNumber, string|array|null $proxy = null): bool|int
    {
        $template = $page->website->getTemplateClass();

        $crawUrl = $page->url;
        if ($pageNumber > 1 && $page->url_with_page) {
            $crawUrl = str_replace(
                ['{page}'],
                [$pageNumber],
                $page->url_with_page
            );
        }

        $items = $this->crawLinksUrl(
            $crawUrl,
            $template,
            (bool) $page->is_resource_page,
            $proxy
        );

        $data = $this->checkAndInsertLinks($items, $page);

        return count($data);
    }

    public function crawLinksUrl(
        string $url,
        CrawlerTemplate $template,
        bool $isResource = false,
        string|array|null $proxy = null
    ): array {
        $linkCrawler = $this->createLinkCrawler();
        if ($proxy) {
            $linkCrawler->withProxy($proxy);
        }

        return $linkCrawler->crawLinksUrl($url, $template, $isResource);
    }

    public function crawContentUrl(string $url, CrawlerTemplate $template, bool $isResource = false): array
    {
        return $this->createContentCrawler()->crawContentsUrl($url, $template, $isResource);
    }

    public function crawContentLink(CrawlerLink $link, string|array|null $proxy = null): CrawlerContent
    {
        $template = $link->website->getTemplateClass();

        $isResource = (bool) $link->page->is_resource_page;

        $contentCrawler = $this->createContentCrawler();
        if ($proxy) {
            $contentCrawler->withProxy($proxy);
        }

        $data = $contentCrawler->crawContentsUrl(
            $link->url,
            $template,
            $isResource
        );

        throw_if(
            empty($data['content']),
            new CrawContentLinkException("Can't get content data link {$link->url}")
        );

        throw_if(
            empty($data['title']),
            new CrawContentLinkException("Can't get title data link {$link->url}")
        );

        DB::beginTransaction();
        try {
            $content = CrawlerContent::where(['link_id' => $link->id, 'lang' => $link->page->lang])->first();
            if ($content) {
                $status = $content->status;
                if ($content->status == CrawlerContent::STATUS_REGET) {
                    $status = CrawlerContent::STATUS_DONE;
                }

                $content->update(
                    [
                        'components' => $data,
                        'status' => $status,
                    ]
                );
            } else {
                $content = CrawlerContent::create(
                    [
                        'components' => $data,
                        'is_source' => true,
                        'link_id' => $link->id,
                        'page_id' => $link->page_id,
                        'lang' => $link->page->lang,
                        'status' => CrawlerContent::STATUS_PENDING,
                        'website_id' => $link->website_id,
                    ]
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $content;
    }

    public function translate(CrawlerContent $content, string $target, string|array|null $proxy = null): CrawlerContent
    {
        if ($currentContent = $content->children()->where(['lang' => $target])->first()) {
            $currentContent->components = $this->translateCrawlerContent($content, $target);
            $currentContent->status = CrawlerContent::STATUS_PENDING;
            $currentContent->save();
            return $currentContent;
        }

        $newContent = $content->replicate();
        $newContent->components = $this->translateCrawlerContent($content, $target, $proxy);
        $newContent->status = CrawlerContent::STATUS_PENDING;
        $newContent->lang = $target;
        $newContent->is_source = false;
        $newContent->post_id = null;
        $newContent->resource_id = null;
        $newContent->save();
        return $newContent;
    }

    public function savePost(CrawlerContent $content, CrawlerLink $link = null): Post|array
    {
        if ($link === null) {
            $link = $content->link;
        }

        $template = $link->website->getTemplateClass();
        $isResource = (bool) $link->page->is_resource_page;
        $queue = config('crawler.queue.crawler');

        if ($isResource) {
            $resource = $this->importResourceData($content->components, $link);

            if (method_exists($template, 'createdResourcesEvent')) {
                $template->createdResourcesEvent($resource, $content->components);
            }

            $updateData = [
                'resource_id' => $resource[0]->id,
                'status' => CrawlerContent::STATUS_DONE,
            ];

            if (!$content->is_source) {
                $updateData['components'] = [];
            }

            $content->update($updateData);

            return $resource;
        }

        $data = $content->components;
        if ($content->post_id) {
            unset($data['thumbnail']);
            $post = $content->post;
            $post->update($data);
        } else {
            $data['type'] = $link->page->post_type;
            $data['locale'] = $content->lang;
            $data['status'] = Post::STATUS_PRIVATE;

            $post = $this->importPostData($data, $link, $template);

            if (method_exists($template, 'createdPostEvent')) {
                $template->createdPostEvent($post, $data);
            }
        }

        $updateData = [
            'post_id' => $post->id,
            'status' => CrawlerContent::STATUS_DONE,
        ];

        if (!$content->is_source) {
            $updateData['components'] = [];
        }

        $content->update($updateData);

        if ($comments = Arr::get($data, 'comments')) {
            //$min = 1;
            foreach ($comments as $comment) {
                AddCommentToPostJob::dispatchSync($post, $comment);

                //$min += random_int(3, 5);
            }
        }

        try {
            event(new PostSuccess($post, $content));
        } catch (\Throwable $e) {
            report($e);
        }

        return $post;
    }

    public function translateCrawlerContent(
        CrawlerContent $content,
        string $target,
        string|array|null $proxy = null
    ): array {
        $translater = $this->createCrawlerContentTranslation($content, $content->lang, $target, $proxy);

        return $translater->translate();
    }

    public function checkAndInsertLinks(array $items, CrawlerPage $page): array
    {
        $colection = collect($items)
            ->map(
                fn ($item) => [
                    'url' => $item,
                    'url_hash' => sha1($item),
                    'website_id' => $page->website->id,
                    'page_id' => $page->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]
            );

        $urls = CrawlerLink::whereIn('url_hash', $colection->pluck('url_hash')->toArray())
            ->get(['url_hash'])
            ->keyBy('url_hash')
            ->toArray();

        $colection = $colection
            ->filter(fn ($url) => is_url($url['url']) && !isset($urls[$url['url_hash']]))
            ->keyBy('url_hash');

        $urlHashs = $colection->keys()->toArray();
        $data = $colection->values()->toArray();

        DB::table(CrawlerLink::getTableName())->lockForUpdate()->insert($data);

        $this->crawlerContents($urlHashs);

        return $data;
    }

    protected function crawlerContents(array $urlHashs): void
    {
        $queue = config('crawler.queue.crawler');

        try {
            $links = CrawlerLink::with(['website', 'page'])
                ->where(['status' => CrawlerLink::STATUS_PENDING])
                ->whereIn('url_hash', $urlHashs)
                ->lockForUpdate()
                ->get();

            $job = 1;
            foreach ($links as $link) {
                ContentCrawlerJob::dispatch($link)->onQueue($queue)
                    ->delay(Carbon::now()->addSeconds($job * 20));

                $link->update(['status' => CrawlerLink::STATUS_PROCESSING]);
                $job++;
            }
        } catch (\Throwable $e) {
            report($e);
        }
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
        throw_if(empty($data['title']), new CrawlerException('Data post empty title'));

        throw_if(empty($data['content']), new CrawlerException('Data post empty content'));

        foreach ($link->page->category_ids ?? [] as $key => $item) {
            $data[$key] = array_merge($item, $data[$key] ?? []);
        }

        $createdBy = User::where(['is_fake' => true])->inRandomOrder()->first();
        if (empty($createdBy)) {
            $createdBy = User::inRandomOrder()->first();
        }

        $postImporter = $this->postImporter->setDownloadThumbnail(false)
            ->setDownloadContentImages(false);

        $postImporter->setCreatedBy($createdBy->id);
        $taxonomies = $this->hookAction->getTaxonomies($data['type']);

        foreach ($taxonomies as $key => $taxonomy) {
            if ($key != 'tags') {
                continue;
            }

            if ($taxs = Arr::get($data, $key)) {
                if (empty($data[$key])) {
                    unset($data[$key]);
                    continue;
                }

                $data[$key] = [];
                foreach ($taxs as $tax) {
                    $data[$key][] = ['name' => $tax, 'slug' => Str::slug($tax)];
                }
            }
        }

        $post = $postImporter->import($data);

        if ($template instanceof TemplateHasResource) {
            $urlPage = $template->getResourceUrlWithPage() ? str_replace(
                ['{post_url}'],
                [$link->url],
                $template->getResourceUrlWithPage()
            ) : null;

            CrawlerPage::firstOrCreate(
                [
                    'url_hash' => sha1($link->url),
                ],
                [
                    'url' => $link->url,
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

    private function createCrawlerContentTranslation(
        CrawlerContent $content,
        string $source,
        string $target,
        string|array|null $proxy = null
    ): CrawlerContentTranslation {
        $translater = new CrawlerContentTranslation($content, $source, $target);
        if ($proxy) {
            return $translater->withProxy($proxy);
        }
        return $translater;
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
