<?php

namespace Juzaweb\Modules\Crawler\Tests\Feature;

use Juzaweb\Modules\Crawler\Http\DataTables\CrawlerLogsDataTable;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;
use Juzaweb\Modules\Crawler\Models\CrawlerPage;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;
use Juzaweb\Modules\Crawler\Tests\TestCase;

class CrawlerLogsDataTableTest extends TestCase
{
    public function testQueryFilters()
    {
        $source = CrawlerSource::create([
            'name' => 'Test Source',
            'active' => true,
            'data_type' => 'html',
            'link_element' => 'a',
            'components' => [],
        ]);

        $page = CrawlerPage::create([
            'source_id' => $source->id,
            'url' => 'http://example.com',
            'active' => true,
        ]);

        $log1 = CrawlerLog::forceCreate([
            'source_id' => $source->id,
            'page_id' => $page->id,
            'status' => 'pending',
            'url' => 'http://example.com/1',
            'url_hash' => sha1('http://example.com/1'),
            'locale' => 'en',
        ]);

        $log2 = CrawlerLog::forceCreate([
            'source_id' => $source->id,
            'page_id' => $page->id,
            'status' => 'completed',
            'url' => 'http://example.com/2',
            'url_hash' => sha1('http://example.com/2'),
            'locale' => 'en',
        ]);

        $dataTable = app(CrawlerLogsDataTable::class);

        // Test Source Filter
        request()->merge(['source_id' => $source->id]);
        $query = $dataTable->query(new CrawlerLog());
        $this->assertEquals(2, $query->count());

        // Test Status Filter
        request()->merge(['status' => 'pending']);
        $query = $dataTable->query(new CrawlerLog());
        $this->assertEquals(1, $query->count());
        $this->assertEquals($log1->id, $query->first()->id);

        // Test Page Filter
        request()->merge(['page_id' => $page->id]);
        $query = $dataTable->query(new CrawlerLog());
        $this->assertEquals(1, $query->count());
        $this->assertEquals($log1->id, $query->first()->id);
    }
}
