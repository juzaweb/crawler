<?php

namespace Juzaweb\Modules\Crawler\Tests\Feature;

use Illuminate\Support\Str;
use Juzaweb\Modules\Crawler\Tests\TestCase;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;
use Juzaweb\Modules\Crawler\Http\DataTables\CrawlerSourcesDataTable;
use Juzaweb\Modules\Core\Models\User;

class CrawlerSourceBulkActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create a super admin user
        $user = new User();
        $user->name = 'Admin';
        $user->email = 'admin@test.com';
        $user->password = bcrypt('password');
        $user->is_super_admin = 1;
        $user->email_verified_at = now();
        $user->save();

        $this->actingAs($user);
    }

    public function test_bulk_activate()
    {
        $source = new CrawlerSource();
        $source->id = Str::uuid();
        $source->name = 'Test Source Inactive';
        $source->data_type = 'post';
        $source->components = [];
        $source->active = false;
        $source->save();

        $response = $this->postJson('admin/crawler-sources/bulk', [
            'action' => 'activate',
            'ids' => [$source->id],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('crawler_sources', [
            'id' => $source->id,
            'active' => 1,
        ]);
    }

    public function test_bulk_deactivate()
    {
        $source = new CrawlerSource();
        $source->id = Str::uuid();
        $source->name = 'Test Source Active';
        $source->data_type = 'post';
        $source->components = [];
        $source->active = true;
        $source->save();

        $response = $this->postJson('admin/crawler-sources/bulk', [
            'action' => 'deactivate',
            'ids' => [$source->id],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('crawler_sources', [
            'id' => $source->id,
            'active' => 0,
        ]);
    }

    public function test_datatable_has_bulk_actions()
    {
        $dataTable = new CrawlerSourcesDataTable();
        $bulkActions = $dataTable->bulkActions();

        $actionKeys = [];
        foreach ($bulkActions as $action) {
             if (method_exists($action, 'toArray')) {
                 $data = $action->toArray();
                 if (isset($data['action'])) {
                     $actionKeys[] = $data['action'];
                 }
             } else {
                 // Try to get protected property via reflection
                 $reflection = new \ReflectionClass($action);
                 if ($reflection->hasProperty('action')) {
                     $property = $reflection->getProperty('action');
                     $property->setAccessible(true);
                     $actionKeys[] = $property->getValue($action);
                 }
             }
        }

        $this->assertContains('activate', $actionKeys, 'Activate bulk action missing');
        $this->assertContains('deactivate', $actionKeys, 'Deactivate bulk action missing');
    }
}
