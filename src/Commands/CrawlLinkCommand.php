<?php
/**
 * LARABIZ CMS - Full SPA Laravel CMS
 *
 * @package    larabizcms/larabiz
 * @author     The Anh Dang
 * @link       https://larabiz.com
 */

namespace Juzaweb\Modules\Crawler\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Juzaweb\Modules\Crawler\Enums\CrawlerLogStatus;
use Juzaweb\Modules\Crawler\Facades\Crawler;
use Juzaweb\Modules\Crawler\Link;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;
use Symfony\Component\Console\Input\InputOption;

class CrawlLinkCommand extends Command
{
    protected $name = 'crawl:links';

    protected $description = 'Crawl links.';

    public function handle(): int
    {
        $limit = $this->option('limit');

        $links = CrawlerLog::with([
            'source',
            'page.taxonomies.translations' => function ($query) {
                $query->where('locale', 'en');
            }
        ])
            ->where(['active' => true, 'status' => CrawlerLogStatus::PENDING])
            ->limit($limit)
            ->get();

        $crawlLinks = collect();
        $links->each(
            function (CrawlerLog $link) use ($crawlLinks) {
                $linkCrawler = new Link(
                    $link->url,
                    [
                        'title' => new StringElement($link->source->getTitleElement()),
                        'content' => HtmlElement::make($link->source->getContentElement())
                            ->removeInternalLinks($link->url)
                            ->removeElements($link->source->remove_elements ?? []),
                        'thumbnail' => new StringElement(
                            $link->source->getThumbnailElement(),
                            'content'
                        ),
                    ]
                );

                $crawlLinks->push($linkCrawler);
            }
        );

        $results = Crawler::crawl($crawlLinks)->getResult();
        $taxonomies = Taxonomy::withTranslation('en')
            ->get()
            ->pluck('name', 'id')
            ->toArray();

        $insertedIds = DB::transaction(
            function () use ($results, $links, $taxonomies) {
                $contents = [];
                $contentTranslations = [];
                $contentTaxonomies = [];
                $comments = [];
                $commentTranslations = [];

                foreach ($results as $key => $result) {
                    $link = $links[$key];
                    if (isset($result['error'])) {
                        $link->update(['status' => LinkStatus::ERROR, 'error' => $result['error']]);
                        $this->error($result['error']);
                        continue;
                    }

                    $link->update(['status' => LinkStatus::CRAWLED]);
                    $pageLabels = $link->page->taxonomies->pluck('name', 'id')->toArray();

                    $labels = [];
                    $title = $result['title'];

                    if (mb_strlen($title) > 150) {
                        continue;
                    }

                    // Add more labels match title
                    foreach ($taxonomies as $id => $taxonomy) {
                        if (!has_word($taxonomy, $title)) {
                            continue;
                        }

                        if (in_array($taxonomy, $pageLabels)) {
                            continue;
                        }

                        $labels[$id] = $taxonomy;
                    }

                    $contents[] = [
                        'link_id' => $link->id,
                        'page_id' => $link->page_id,
                        'source_id' => $link->source_id,
                        'thumbnail' => $result['thumbnail'],
                        'username' => $result['username'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $contentTranslations[] = [
                        'title' => $title,
                        'content' => $result['content'],
                        'locale' => $link->page->locale,
                        'link_id' => $link->id,
                        'is_source' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    foreach (array_keys($labels) as $labelId) {
                        $contentTaxonomies[] = [
                            'link_id' => $link->id,
                            'taxonomy_id' => $labelId,
                        ];
                    }

                    if (isset($result['comments']) && count($result['comments']) > 0) {
                        foreach ($result['comments'] as $comment) {
                            $comments[] = [
                                'link_id' => $link->id,
                                'username' => $comment['username'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];

                            $commentTranslations[] = [
                                'content' => $comment['content'],
                                'locale' => $link->page->locale,
                                'is_source' => 1,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }

                    $this->info("Crawled {$link->url}");
                }

                DB::table('crawler_contents')->insert($contents);

                $insertedIds = DB::table('crawler_contents')
                    ->whereIn('link_id', Arr::pluck($contents, 'link_id'))
                    ->get(['id', 'link_id'])
                    ->pluck('id', 'link_id')
                    ->toArray();

                $contentTranslations = array_map(
                    function ($contentTranslation) use ($insertedIds) {
                        $contentTranslation['crawler_content_id'] = $insertedIds[$contentTranslation['link_id']];
                        unset($contentTranslation['link_id']);
                        return $contentTranslation;
                    },
                    $contentTranslations
                );

                DB::table('crawler_content_translations')->insert($contentTranslations);

                $contentTaxonomies = array_map(
                    function ($contentTaxonomy) use ($insertedIds) {
                        $contentTaxonomy['content_id'] = $insertedIds[$contentTaxonomy['link_id']];
                        unset($contentTaxonomy['link_id']);
                        return $contentTaxonomy;
                    },
                    $contentTaxonomies
                );

                DB::table('crawler_content_has_taxonomies')->insert($contentTaxonomies);

                if ($comments) {
                    $comments = array_map(
                        function ($comment) use ($insertedIds) {
                            $comment['content_id'] = $insertedIds[$comment['link_id']];
                            unset($comment['link_id']);
                            return $comment;
                        },
                        $comments
                    );

                    foreach ($comments as $index => $comment) {
                        $newCommentId = DB::table('crawler_content_comments')->insertGetId($comment);
                        $commentTranslation = $commentTranslations[$index];
                        $commentTranslation['content_comment_id'] = $newCommentId;

                        DB::table('crawler_content_comment_translations')->insert($commentTranslation);
                    }
                }

                return $insertedIds;
            }
        );

        $this->info("Inserted " . count($insertedIds) . " contents");

        // Translate contents
        $delay = 10;
        $locales = config('crawler.translate.locales');

        foreach ($locales as $locale) {
            Content::with(['page', 'comments.translations'])
                ->whereIn('id', array_values($insertedIds))
                ->whereDoesntHave('translations', fn($q) => $q->where('locale', $locale))
                ->chunkById(100, function ($contents) use (&$delay, $locale) {
                    foreach ($contents as $content) {
                        DB::transaction(function () use ($content, $locale, &$delay) {
                            Translate::dispatch($content, $locale, $content->page->locale)
                                ->onQueue(config('crawler.queue.translate'))
                                ->delay($delay);

                            foreach ($content->comments as $comment) {
                                $delay += random_int(100, 600);
                                Translate::dispatch($comment, $locale, $content->page->locale)
                                    ->onQueue(config('crawler.queue.translate'))
                                    ->delay($delay);

                                $comment->translateHistories()->create(
                                    [
                                        'locale' => $locale,
                                    ]
                                );

                                $this->info("-- Translating comment {$content->id} to {$locale}");
                            }

                            $content->translateHistories()->create(
                                [
                                    'locale' => $locale,
                                ]
                            );
                        });

                        $delay += 10;

                        $this->info("- Translating {$content->id} to {$locale}");
                    }
                });
        }

        return self::SUCCESS;
    }

    protected function getOptions(): array
    {
        return [
            ['limit', null, InputOption::VALUE_OPTIONAL, 'Limit crawling.', 50],
        ];
    }
}
