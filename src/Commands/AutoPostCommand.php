<?php

namespace Juzaweb\Crawler\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerContent;
use Symfony\Component\Console\Input\InputOption;

class AutoPostCommand extends Command
{
    protected $name = 'crawler:posts';

    protected $description = 'Command post crawler content.';

    public function handle()
    {
        $contents = CrawlerContent::with(['link.page', 'link.website'])
            ->where(['status' => CrawlerContent::STATUS_PENDING])
            ->whereNull('post_id')
            ->whereNull('resource_id')
            ->orderBy('id', 'ASC')
            ->limit((int) $this->option('limit'))
            ->get();

        $skipSource = (bool) get_config('crawler_skip_origin_content', 0);
        $crawler = app(CrawlerContract::class);
        foreach ($contents as $content) {
            if ($content->is_source && $skipSource) {
                $content->update(['status' => CrawlerContent::STATUS_DONE]);
                continue;
            }

            DB::beginTransaction();
            try {
                $post = $crawler->savePost($content);

                $type = $content->link->page->is_resource_page ? 'Resource' : 'Post';

                $this->info("Created {$type} {$post->id}");

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                report($e);
                $content->update(
                    [
                        'status' => CrawlerContent::STATUS_ERROR,
                    ]
                );
            }
        }
    }

    protected function getOptions(): array
    {
        return [
            ['limit', null, InputOption::VALUE_OPTIONAL, 'The limit rows crawl per run.', 20],
        ];
    }
}
