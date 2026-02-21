# Juzaweb Crawler Module

A powerful and extensible crawler module for Juzaweb CMS that automates content aggregation from external websites.

## Features

- **Configurable Sources**: Define crawl targets using CSS selectors and Regex directly from the Admin interface.
- **Concurrent Crawling**: Uses Guzzle Pool for high-performance, concurrent requests.
- **Flexible Data Extraction**: Extract data as Text, HTML, or Arrays using precise CSS selectors.
- **Content Cleaning**: Automatically remove unwanted elements (ads, scripts, etc.) from crawled content.
- **Extensible Data Types**: Map crawled data to any model (Posts, Products, etc.) by implementing custom Data Types.
- **Admin Integration**: Full management of Sources, Pages, and Logs via the Juzaweb Admin Panel.

## Installation

Install the package via Composer:

```bash
composer require juzaweb/crawler
```

Run the migrations to create the necessary database tables:

```bash
php artisan migrate
```

(Optional) Publish the configuration file and assets:

```bash
php artisan vendor:publish --tag=crawler-config
php artisan vendor:publish --tag=crawler-assets
```

## Usage Workflow

The crawling process follows a 3-step workflow: **Discover -> Crawl -> Process**.

### 1. Create a Crawler Source

Navigate to **Crawler > Sources** in the Admin Panel and create a new Source.

- **Name**: A descriptive name for the source.
- **Data Type**: The type of content to create (e.g., "Post").
- **Link Element**: The CSS selector to find links to individual pages (e.g., `.post-list .post-title a`).
- **Link Regex**: (Optional) A regex pattern to filter the extracted links.
- **Components**: Define what data to extract from the detail page.
    - **Element**: CSS selector for the data (e.g., `h1.title`).
    - **Attribute**: (Optional) Attribute to extract (e.g., `src` for images). Leave empty for text/html.
    - **Format**: `Text`, `HTML`, or `Array`.
- **Removes**: CSS selectors for elements to remove from the extracted content (e.g., `.ad-banner`).

### 2. Add Seed Pages

In the Source edit page, add **Seed Pages**. These are the starting points for the crawler (e.g., a blog category page or a search result page).

- **URL**: The URL to start crawling from.
- **Next Page**: (Optional) Pattern for pagination (e.g., `page/:page`).

### 3. Run the Crawler

You can run the crawler manually using Artisan commands or schedule them.

#### Step 1: Discover Links
Visits the Seed Pages and extracts links matching the `Link Element` selector.
```bash
php artisan crawl:pages
```

#### Step 2: Crawl Content
Visits the discovered links and extracts data based on the Source's `Components`.
```bash
php artisan crawl:links
```

#### Step 3: Process to Post
Converts the crawled data (Logs) into actual CMS content (e.g., Posts) using the `Data Type` handler.
```bash
php artisan crawl:content-to-post
```

## Scheduling

To automate the crawling process, add the commands to your `app/Console/Kernel.php` (or use the Scheduler in your server environment).

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Discover new links every hour
    $schedule->command('crawl:pages')->hourly();

    // Crawl content every 15 minutes
    $schedule->command('crawl:links')->everyFifteenMinutes();

    // Process posts every 30 minutes
    $schedule->command('crawl:content-to-post')->everyThirtyMinutes();
}
```

## Extending (Custom Data Types)

You can define custom Data Types to save crawled data to different models (e.g., Products, Videos).

### 1. Implement `CrawlerDataType`

Create a class that implements `Juzaweb\Modules\Crawler\Contracts\CrawlerDataType`.

```php
namespace App\Crawler;

use Juzaweb\Modules\Crawler\Contracts\CrawlerDataType;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;
use Illuminate\Database\Eloquent\Model;

class ProductDataType implements CrawlerDataType
{
    public function save(CrawlerLog $crawlerLog): Model
    {
        $data = $crawlerLog->content_json;

        // Logic to save data to your Product model
        $product = new \App\Models\Product();
        $product->name = $data['title'];
        $product->price = $data['price'];
        $product->description = $data['content'];
        $product->save();

        return $product;
    }

    public function components(): array
    {
        return [
            'title' => [
                'type' => 'text',
                'label' => 'Title',
            ],
            'price' => [
                'type' => 'text',
                'label' => 'Price',
            ],
            'content' => [
                'type' => 'html',
                'label' => 'Description',
            ],
        ];
    }

    public function getLabel(): string
    {
        return 'Products';
    }

    // ... implement other methods
}
```

### 2. Register the Data Type

Register your custom Data Type in a Service Provider (e.g., `AppServiceProvider` or a custom one).

```php
use Juzaweb\Modules\Crawler\Facades\Crawler;
use App\Crawler\ProductDataType;

public function boot()
{
    Crawler::registerDataType('product', function () {
        return new ProductDataType();
    });
}
```

Now, "Products" will appear as a Data Type option when creating a Crawler Source.
