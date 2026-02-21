<?php

namespace Juzaweb\Modules\Crawler\Tests\Feature;

use Illuminate\Support\Str;
use Juzaweb\Modules\Core\Models\User;
use Juzaweb\Modules\Crawler\Enums\CrawlerLogStatus;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;
use Juzaweb\Modules\Crawler\Models\CrawlerPage;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;
use Juzaweb\Modules\Crawler\Tests\TestCase;

class CrawlerLogControllerTest extends TestCase
{
    public function test_bulk_retry_action()
    {
        // Create Admin User
        $user = new User();
        $user->name = 'Admin';
        $user->email = 'admin@example.com';
        $user->password = bcrypt('password');
        $user->is_super_admin = 1;
        $user->email_verified_at = now();
        $user->save();

        $this->actingAs($user);

        // Create Source
        $source = new CrawlerSource();
        $source->id = Str::uuid();
        $source->name = 'Test Source';
        $source->data_type = 'post';
        $source->components = [];
        $source->save();

        // Create Page
        $page = new CrawlerPage();
        $page->id = Str::uuid();
        $page->source_id = $source->id;
        $page->url = 'http://example.com/retry';
        $page->url_hash = sha1('http://example.com/retry');
        $page->locale = 'en';
        $page->save();

        // Create Log with FAILED status
        $log = new CrawlerLog();
        $log->url = 'http://example.com/post/retry';
        $log->url_hash = hash('sha256', 'http://example.com/post/retry');
        $log->source_id = $source->id;
        $log->page_id = $page->id;
        $log->status = CrawlerLogStatus::FAILED;
        $log->error = ['msg' => 'Error message'];
        $log->locale = 'en';
        $log->save();

        // Send Bulk Retry Request
        $response = $this->postJson('admin/crawler-logs/bulk', [
            'action' => 'retry',
            'ids' => [$log->id],
        ]);

        $response->assertOk();

        // Check DB
        $log->refresh();
        $this->assertEquals(CrawlerLogStatus::PENDING, $log->status);
        $this->assertNull($log->error);
    }
}
