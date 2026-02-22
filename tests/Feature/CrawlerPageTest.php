<?php

namespace Juzaweb\Modules\Crawler\Tests\Feature;

use Juzaweb\Modules\Crawler\Tests\TestCase;
use Juzaweb\Modules\Core\Models\User;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;
use Juzaweb\Modules\Crawler\Models\CrawlerPage;
use Illuminate\Support\Str;

class CrawlerPageTest extends TestCase
{
    protected $admin;
    protected $source;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'is_super_admin' => 1,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($this->admin);

        // Create a source directly (bypassing controller validation requiring registered data type)
        $this->source = CrawlerSource::create([
            'name' => 'Test Source',
            'data_type' => 'post',
            'components' => [],
            'active' => 1,
        ]);
    }

    public function test_index()
    {
        $response = $this->get("/admin/crawler-sources/{$this->source->id}/pages");

        $response->assertStatus(200);
    }

    public function test_create()
    {
        $response = $this->get("/admin/crawler-sources/{$this->source->id}/pages/create");

        $response->assertStatus(200);
    }

    public function test_store()
    {
        $response = $this->post("/admin/crawler-sources/{$this->source->id}/pages", [
            'url' => 'http://example.com/page',
            'url_with_page' => 'http://example.com/page?page={page}',
            'next_page' => 'span.next > a',
            'active' => 1,
            'locale' => 'en',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(action([\Juzaweb\Modules\Crawler\Http\Controllers\CrawlerPageController::class, 'index'], [$this->source->id]));

        $this->assertDatabaseHas('crawler_pages', [
            'url' => 'http://example.com/page',
            'source_id' => $this->source->id,
            'active' => 1,
        ]);
    }

    public function test_edit()
    {
        $page = CrawlerPage::create([
            'source_id' => $this->source->id,
            'url' => 'http://example.com/page',
            'url_hash' => sha1('http://example.com/page'),
            'locale' => 'en',
        ]);

        $response = $this->get("/admin/crawler-sources/{$this->source->id}/pages/{$page->id}/edit");

        $response->assertStatus(200);
    }

    public function test_update()
    {
        $page = CrawlerPage::create([
            'source_id' => $this->source->id,
            'url' => 'http://example.com/page',
            'url_hash' => sha1('http://example.com/page'),
            'locale' => 'en',
        ]);

        $response = $this->put("/admin/crawler-sources/{$this->source->id}/pages/{$page->id}", [
            'url' => 'http://example.com/updated',
            'url_with_page' => 'http://example.com/updated?page={page}',
            'next_page' => 'span.next > a',
            'active' => 1,
            'locale' => 'en',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(action([\Juzaweb\Modules\Crawler\Http\Controllers\CrawlerPageController::class, 'index'], [$this->source->id]));

        $this->assertDatabaseHas('crawler_pages', [
            'id' => $page->id,
            'url' => 'http://example.com/updated',
        ]);
    }

    public function test_bulk_delete()
    {
        $page = CrawlerPage::create([
            'source_id' => $this->source->id,
            'url' => 'http://example.com/page',
            'url_hash' => sha1('http://example.com/page'),
            'locale' => 'en',
        ]);

        $response = $this->postJson("/admin/crawler-sources/{$this->source->id}/pages/bulk", [
            'ids' => [$page->id],
            'action' => 'delete',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('crawler_pages', [
            'id' => $page->id,
        ]);
    }
}
