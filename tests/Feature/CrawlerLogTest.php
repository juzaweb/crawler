<?php

namespace Juzaweb\Modules\Crawler\Tests\Feature;

use Juzaweb\Modules\Crawler\Tests\TestCase;
use Juzaweb\Modules\Core\Models\User;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;
use Juzaweb\Modules\Crawler\Models\CrawlerPage;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;
use Juzaweb\Modules\Crawler\Enums\CrawlerLogStatus;

class CrawlerLogTest extends TestCase
{
    protected $admin;
    protected $source;
    protected $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'is_super_admin' => 1,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($this->admin);

        $this->source = CrawlerSource::create([
            'name' => 'Test Source',
            'data_type' => 'post',
            'components' => [],
            'active' => 1,
        ]);

        $this->page = CrawlerPage::create([
            'source_id' => $this->source->id,
            'url' => 'http://example.com/page',
            'url_hash' => sha1('http://example.com/page'),
            'locale' => 'en',
        ]);
    }

    public function test_index()
    {
        $response = $this->get('/admin/crawler-logs');

        $response->assertStatus(200);
    }

    public function test_edit()
    {
        $log = CrawlerLog::create([
            'source_id' => $this->source->id,
            'page_id' => $this->page->id,
            'url' => 'http://example.com/log',
            'url_hash' => sha1('http://example.com/log'),
            'status' => CrawlerLogStatus::PENDING,
            'locale' => 'en',
        ]);

        $response = $this->get("/admin/crawler-logs/{$log->id}/edit");

        $response->assertStatus(200);
    }

    public function test_bulk_retry()
    {
        $log = CrawlerLog::create([
            'source_id' => $this->source->id,
            'page_id' => $this->page->id,
            'url' => 'http://example.com/log-retry',
            'url_hash' => sha1('http://example.com/log-retry'),
            'status' => CrawlerLogStatus::FAILED,
            'error' => ['msg' => 'Test Error'],
            'locale' => 'en',
        ]);

        $response = $this->postJson('/admin/crawler-logs/bulk', [
            'ids' => [$log->id],
            'action' => 'retry',
        ]);

        $response->assertStatus(200);

        $log->refresh();

        $this->assertEquals(CrawlerLogStatus::PENDING, $log->status);
        $this->assertNull($log->error);
    }

    public function test_bulk_delete()
    {
        $log = CrawlerLog::create([
            'source_id' => $this->source->id,
            'page_id' => $this->page->id,
            'url' => 'http://example.com/log-delete',
            'url_hash' => sha1('http://example.com/log-delete'),
            'status' => CrawlerLogStatus::PENDING,
            'locale' => 'en',
        ]);

        $response = $this->postJson('/admin/crawler-logs/bulk', [
            'ids' => [$log->id],
            'action' => 'delete',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('crawler_logs', [
            'id' => $log->id,
        ]);
    }
}
