<?php

namespace Juzaweb\Modules\Crawler\Tests\Feature;

use Juzaweb\Modules\Crawler\Tests\TestCase;
use Juzaweb\Modules\Core\Models\User;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;
use Juzaweb\Modules\Crawler\Contracts\Crawler;
use Juzaweb\Modules\Crawler\Contracts\CrawlerDataType;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;

class CrawlerSourceTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'is_super_admin' => 1,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($this->admin);

        // Register dummy data type
        app(Crawler::class)->registerDataType('post', function () {
            return new class implements CrawlerDataType {
                public function save(CrawlerLog $crawlerLog): \Illuminate\Database\Eloquent\Model {
                    return new User();
                }
                public function components(): array {
                    return [];
                }
                public function rules(): array {
                    return [];
                }
                public function getModel(): string {
                    return User::class;
                }
                public function getLabel(): string {
                    return 'Post';
                }
                public function getCategoryClass(): ?string {
                    return null;
                }
            };
        });
    }

    public function test_index()
    {
        $response = $this->get('/admin/crawler-sources');

        $response->assertStatus(200);
    }

    public function test_create()
    {
        $response = $this->get('/admin/crawler-sources/create');

        $response->assertStatus(200);
    }

    public function test_store()
    {
        $response = $this->post('/admin/crawler-sources', [
            'name' => 'Test Source',
            'data_type' => 'post',
            'components' => [
                ['name' => 'title', 'element' => 'h1', 'format' => 'text']
            ],
            'crawler_pages' => [],
            'active' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(action([\Juzaweb\Modules\Crawler\Http\Controllers\CrawlerSourceController::class, 'index']));

        $this->assertDatabaseHas('crawler_sources', [
            'name' => 'Test Source',
            'data_type' => 'post',
            'active' => 1,
        ]);
    }

    public function test_edit()
    {
        $source = CrawlerSource::create([
            'name' => 'Test Source',
            'data_type' => 'post',
            'components' => [],
            'active' => 1,
        ]);

        $response = $this->get("/admin/crawler-sources/{$source->id}/edit");

        $response->assertStatus(200);
    }

    public function test_update()
    {
        $source = CrawlerSource::create([
            'name' => 'Test Source',
            'data_type' => 'post',
            'components' => [],
            'active' => 1,
        ]);

        $response = $this->put("/admin/crawler-sources/{$source->id}", [
            'name' => 'Updated Source',
            'data_type' => 'post',
            'components' => [
                 ['name' => 'title', 'element' => 'h1', 'format' => 'text']
            ],
            'crawler_pages' => [],
            'active' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(action([\Juzaweb\Modules\Crawler\Http\Controllers\CrawlerSourceController::class, 'index']));

        $this->assertDatabaseHas('crawler_sources', [
            'id' => $source->id,
            'name' => 'Updated Source',
        ]);
    }

    public function test_bulk_delete()
    {
        $source = CrawlerSource::create([
            'name' => 'Test Source',
            'data_type' => 'post',
            'components' => [],
            'active' => 1,
        ]);

        $response = $this->postJson('/admin/crawler-sources/bulk', [
            'ids' => [$source->id],
            'action' => 'delete',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('crawler_sources', [
            'id' => $source->id,
        ]);
    }

    public function test_get_components()
    {
        $response = $this->get('/admin/crawler-sources/get-components?data_type=post');

        $response->assertStatus(200);
        $response->assertJsonStructure(['html']);
    }
}
