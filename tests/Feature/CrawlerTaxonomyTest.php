<?php

namespace Juzaweb\Modules\Crawler\Tests\Feature;

use Juzaweb\Modules\Crawler\Models\CrawlerPage;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;
use Juzaweb\Modules\Crawler\Models\CrawlerTaxonomy;
use Juzaweb\Modules\Crawler\Tests\TestCase;

class CrawlerTaxonomyTest extends TestCase
{
    public function test_crawler_page_has_taxonomies()
    {
        // Create a source
        $source = new CrawlerSource();
        $source->fill([
            'name' => 'Test Source',
            'active' => true,
            'data_type' => 'post',
            'link_element' => 'a',
            'components' => [],
            'removes' => [],
        ]);
        $source->save();

        // Create a page
        $page = new CrawlerPage();
        $page->fill([
            'source_id' => $source->id,
            'url' => 'https://example.com/page1',
            'locale' => 'en',
        ]);
        $page->save();

        // Create a taxonomy
        $taxonomy = new CrawlerTaxonomy();
        $taxonomy->fill([
            'crawler_page_id' => $page->id,
            'taxonomy_id' => 123,
            'taxonomy_type' => 'categories',
        ]);
        $taxonomy->save();

        // Assert relationship
        $this->assertEquals(1, $page->taxonomies()->count());
        $this->assertEquals(123, $page->taxonomies->first()->taxonomy_id);
    }
}
