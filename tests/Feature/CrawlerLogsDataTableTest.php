<?php

namespace Juzaweb\Modules\Crawler\Tests\Feature;

use Illuminate\Support\Str;
use Juzaweb\Modules\Crawler\Tests\TestCase;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;
use Juzaweb\Modules\Crawler\Models\CrawlerPage;
use Juzaweb\Modules\Crawler\Enums\CrawlerLogStatus;
use Juzaweb\Modules\Crawler\Http\DataTables\CrawlerLogsDataTable;
use Yajra\DataTables\Facades\DataTables;

class CrawlerLogsDataTableTest extends TestCase
{
    public function test_render_columns_with_error_status()
    {
        // 1. Create Source
        $source = new CrawlerSource();
        $source->id = Str::uuid();
        $source->name = 'Test Source';
        $source->data_type = 'post';
        $source->components = [];
        $source->save();

        // 2. Create Page
        $page = new CrawlerPage();
        $page->id = Str::uuid();
        $page->source_id = $source->id;
        $page->url = 'http://example.com';
        $page->url_hash = sha1('http://example.com');
        $page->locale = 'en';
        $page->save();

        // 3. Create Log with FAILED status and error
        $log = new CrawlerLog();
        $log->url = 'http://example.com/post';
        $log->url_hash = hash('sha256', 'http://example.com/post');
        $log->source_id = $source->id;
        $log->page_id = $page->id;
        $log->status = CrawlerLogStatus::FAILED;
        $log->error = ['msg' => 'Test Error'];
        $log->locale = 'en';
        $log->save();

        // 4. Instantiate DataTable
        $dataTable = new CrawlerLogsDataTable();

        // 5. Query
        $query = $dataTable->query($log);

        // 6. Use Yajra DataTables to process
        $eloquentDataTable = DataTables::eloquent($query);

        // Attach callbacks
        $dataTable->renderColumns($eloquentDataTable);
        $eloquentDataTable->rawColumns(['status']);

        // 7. Get result
        $response = $eloquentDataTable->make(true);
        $data = $response->getData(true);

        $this->assertNotEmpty($data['data']);
        $row = $data['data'][0];

        // 8. Assertions
        $this->assertStringContainsString('show-log-error', $row['status']);
        $this->assertStringContainsString('data-error="', $row['status']);
        $this->assertStringContainsString('&quot;msg&quot;:&quot;Test Error&quot;', $row['status']);
    }

    public function test_render_columns_with_success_status()
    {
        // 1. Create Source
        $source = new CrawlerSource();
        $source->id = Str::uuid();
        $source->name = 'Test Source 2';
        $source->data_type = 'post';
        $source->components = [];
        $source->save();

        // 2. Create Page
        $page = new CrawlerPage();
        $page->id = Str::uuid();
        $page->source_id = $source->id;
        $page->url = 'http://example.com/2';
        $page->url_hash = sha1('http://example.com/2');
        $page->locale = 'en';
        $page->save();

        // 3. Create Log with COMPLETED status
        $log = new CrawlerLog();
        $log->url = 'http://example.com/post/2';
        $log->url_hash = hash('sha256', 'http://example.com/post/2');
        $log->source_id = $source->id;
        $log->page_id = $page->id;
        $log->status = CrawlerLogStatus::COMPLETED;
        $log->error = null;
        $log->locale = 'en';
        $log->save();

        // 4. Instantiate DataTable
        $dataTable = new CrawlerLogsDataTable();

        $query = $dataTable->query($log);
        $eloquentDataTable = DataTables::eloquent($query);
        $dataTable->renderColumns($eloquentDataTable);

        $response = $eloquentDataTable->make(true);
        $data = $response->getData(true);

        $row = $data['data'][0];

        $this->assertStringNotContainsString('show-log-error', $row['status']);
        $this->assertStringNotContainsString('data-error', $row['status']);
    }
}
