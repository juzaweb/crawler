<?php

namespace Juzaweb\Modules\Crawler\Tests\Feature;

use Juzaweb\Modules\Crawler\Http\DataTables\CrawlerLogsDataTable;
use Juzaweb\Modules\Crawler\Tests\TestCase;

class CrawlerLogsDataTableTest extends TestCase
{
    public function testSearchFieldsStructure()
    {
        $dataTable = app(CrawlerLogsDataTable::class);

        $searchFields = $dataTable->searchFields();

        $this->assertIsArray($searchFields);
        $this->assertArrayHasKey('keyword', $searchFields);
        $this->assertArrayHasKey('source_id', $searchFields);
        $this->assertArrayHasKey('page_id', $searchFields);
        $this->assertArrayHasKey('status', $searchFields);

        $this->assertEquals('select', $searchFields['source_id']['type']);
        $this->assertEquals('select', $searchFields['page_id']['type']);
        $this->assertEquals('select', $searchFields['status']['type']);
    }
}
