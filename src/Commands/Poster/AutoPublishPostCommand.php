<?php

namespace Juzaweb\Crawler\Commands\Poster;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Juzaweb\Backend\Models\Post;
use Juzaweb\Backend\Models\Taxonomy;

class AutoPublishPostCommand extends Command
{
    protected $signature = 'crawler:auto-publish-posts';

    protected $description = 'Auto publish posts command.';

    public function handle(): void
    {
        if (!get_config('crawler_auto_publish_posts')) {
            return;
        }

        $limit = get_config('crawler_auto_publish_posts_per_day', 50);
        $limit = (int) ceil($limit / 24);

        $maxId = $this->getPostBuilder()->skip($limit)->take(1)->value('id');
        $updateDate = now()->subSeconds($limit);

        Post::setFlushCacheOnUpdate(false);
        Taxonomy::setFlushCacheOnUpdate(false);

        $seconds = 0;
        $this->getPostBuilder()
            ->when($maxId, fn ($q) => $q->where('id', '<', $maxId))
            ->chunkById(
                300,
                function ($posts) use ($updateDate, &$seconds) {
                    foreach ($posts as $post) {
                        $post->setAttribute('status', Post::STATUS_PUBLISH);
                        $post->setAttribute('updated_at', $updateDate->addSeconds($seconds));
                        $post->save();
                        $seconds++;
                    }

                    $this->info("Publish ". $posts->count() ." posts success.");
                }
            );
    }

    private function getPostBuilder(): EloquentBuilder|Builder
    {
        return Post::where(['status' => Post::STATUS_PRIVATE, 'type' => 'posts', 'locale' => 'vi'])
            ->orderBy('id', 'ASC');
    }
}
