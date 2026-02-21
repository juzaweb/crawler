<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Crawler\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Juzaweb\Modules\Crawler\Enums\CrawlerLogStatus;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;
use Symfony\Component\Console\Input\InputOption;

class ContentToPostCommand extends Command
{
    protected $name = 'crawl:content-to-post';

    protected $description = 'Create posts from contents crawled.';

    public function handle(): int
    {
        $limit = $this->option('limit');
        $total = 0;

        CrawlerLog::with([
            'page',
            'source',
        ])
            ->whereNull('post_id')
            ->where(['status' => CrawlerLogStatus::CRAWLED])
            ->chunkById(100, function ($contents) use (&$total, $limit) {
                /**
                 * @var CrawlerLog $content
                 */
                foreach ($contents as $content) {
                    if ($limit && $total >= $limit) {
                        return false;
                    }

                    try {
                        DB::transaction(
                            function () use ($content) {
                                $post = $content->source->getDataType()?->save($content);
                                $content->update([
                                    'status' => CrawlerLogStatus::COMPLETED,
                                    'post_id' => $post->id,
                                    'post_type' => $post->getMorphClass(),
                                ]);
                            }
                        );

                        $this->info("Creating post {$content->id} from content {$content->id}");
                    } catch (\Exception $e) {
                        report($e);
                        $content->update([
                            'status' => CrawlerLogStatus::FAILED_POSTING,
                            'error' => get_error_by_exception($e),
                        ]);

                        $this->error("Failed to create post from content {$content->id}: " . $e->getMessage());
                    }

                    $total++;
                }
            });

        return Command::SUCCESS;
    }

    protected function getOptions(): array
    {
        return [
            ['limit', null, InputOption::VALUE_OPTIONAL, 'The number of contents to process.', 10],
        ];
    }
}
