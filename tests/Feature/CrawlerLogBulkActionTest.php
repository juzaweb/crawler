<?php

namespace Juzaweb\Modules\Crawler\Tests\Feature;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Juzaweb\Modules\Core\Models\User;
use Juzaweb\Modules\Crawler\Jobs\PostJob;
use Juzaweb\Modules\Crawler\Enums\CrawlerLogStatus;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;
use Juzaweb\Modules\Crawler\Models\CrawlerPage;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;
use Juzaweb\Modules\Crawler\Tests\TestCase;

class CrawlerLogBulkActionTest extends TestCase
{
    public function test_repost_bulk_action_dispatches_post_job_and_updates_status()
    {
        Bus::fake();

        // 1. Create Admin User
        $user = new User();
        $user->name = 'Admin';
        $user->email = 'admin@test.com';
        $user->password = bcrypt('password');
        $user->is_super_admin = 1;
        $user->email_verified_at = now();
        $user->save();

        $this->actingAs($user);

        // 2. Create Source
        $source = new CrawlerSource();
        $source->id = Str::uuid();
        $source->name = 'Test Source';
        $source->data_type = 'post';
        $source->components = [];
        $source->save();

        // 3. Create Page
        $page = new CrawlerPage();
        $page->id = Str::uuid();
        $page->source_id = $source->id;
        $page->url = 'http://example.com';
        $page->url_hash = sha1('http://example.com');
        $page->locale = 'en';
        $page->save();

        // 4. Create Log
        $log = new CrawlerLog();
        $log->url = 'http://example.com/post';
        $log->url_hash = hash('sha256', 'http://example.com/post');
        $log->source_id = $source->id;
        $log->page_id = $page->id;
        $log->status = CrawlerLogStatus::FAILED;
        $log->error = ['msg' => 'Test Error'];
        $log->locale = 'en';
        $log->save();

        // 5. Perform Bulk Action
        $response = $this->postJson(admin_url('crawler-logs/bulk'), [
            'ids' => [$log->id],
            'action' => 'repost',
        ]);

        $response->assertStatus(200);

        // 6. Assert Status Updated
        $log->refresh();
        $this->assertEquals(CrawlerLogStatus::POSTING, $log->status);
        $this->assertNull($log->error);

        // 7. Assert Job Dispatched
        Bus::assertDispatched(PostJob::class, function ($job) use ($log) {
            // Using reflection to access protected property if needed,
            // or relying on public property if it is one.
            // PostJob uses constructor promotion: protected CrawlerLog $crawlerLog
            // So we can check via reflection or if it exposes it.

            $reflection = new \ReflectionClass($job);
            $property = $reflection->getProperty('crawlerLog');
            $property->setAccessible(true);
            $jobLog = $property->getValue($job);

            return $jobLog->id === $log->id;
        });
    }
}
