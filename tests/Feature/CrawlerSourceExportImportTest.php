<?php

namespace Juzaweb\Modules\Crawler\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Juzaweb\Modules\Core\Models\User;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;
use Juzaweb\Modules\Crawler\Tests\TestCase;

class CrawlerSourceExportImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create user
        $user = new User();
        $user->forceFill([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'is_super_admin' => 1,
            'email_verified_at' => now(),
        ])->save();

        $this->actingAs($user);
    }

    public function test_export_sources()
    {
        $source = CrawlerSource::create([
            'name' => 'Export Source',
            'active' => 1,
            'data_type' => 'post',
            'components' => [],
            'removes' => [],
        ]);

        $source->pages()->create([
            'url' => 'http://example.com/export',
            'active' => 1,
            'locale' => 'en',
        ]);

        $response = $this->get(route('admin.crawler-sources.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');

        $content = $response->getContent();
        $this->assertStringContainsString('<name>Export Source</name>', $content);
        $this->assertStringContainsString('<url>http://example.com/export</url>', $content);
    }

    public function test_import_sources()
    {
        $xml = '<?xml version="1.0"?>
<crawler_data>
    <source>
        <name>Imported Source</name>
        <active>1</active>
        <data_type>post</data_type>
        <link_element>.link</link_element>
        <link_regex></link_regex>
        <components>
            <component key="title">
                <name>Title</name>
                <element>.title</element>
                <attr></attr>
                <format>text</format>
            </component>
        </components>
        <removes>
            <remove>.ads</remove>
        </removes>
        <pages>
            <page>
                <url>http://imported.com</url>
                <url_with_page>http://imported.com/page/:page</url_with_page>
                <locale>en</locale>
                <active>1</active>
            </page>
        </pages>
    </source>
</crawler_data>';

        $file = UploadedFile::fake()->createWithContent('import.xml', $xml);

        $url = route('admin.crawler-sources.import');

        $response = $this->post($url, [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.crawler-sources.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('crawler_sources', [
            'name' => 'Imported Source',
            'data_type' => 'post',
        ]);

        $source = CrawlerSource::where('name', 'Imported Source')->first();
        $this->assertNotNull($source);

        // Verify components array structure
        $this->assertIsArray($source->components);
        $this->assertArrayHasKey('title', $source->components);
        $this->assertEquals('Title', $source->components['title']['name']);

        // Verify removes array
        $this->assertIsArray($source->removes);
        $this->assertEquals('.ads', $source->removes[0]);

        $this->assertDatabaseHas('crawler_pages', [
            'source_id' => $source->id,
            'url' => 'http://imported.com',
        ]);
    }
}
