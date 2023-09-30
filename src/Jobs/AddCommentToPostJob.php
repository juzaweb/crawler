<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/juzacms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Juzaweb\Backend\Models\Comment;
use Juzaweb\Backend\Models\Post;
use Juzaweb\CMS\Models\User;
use Juzaweb\Crawler\Events\AddCommandToPostSuccess;
use Juzaweb\Crawler\Models\CrawlerContent;

class AddCommentToPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;

    public function __construct(protected Post $post, protected string|array $comment)
    {
    }

    public function handle(): void
    {
        if (is_array($this->comment)) {
            $comment = $this->comment['content'];
            $author = Arr::get($this->comment, 'author');

            if ($author) {
                $content = CrawlerContent::where(['is_source' => true])
                    ->whereHas(
                        'children',
                        fn ($q) => $q->where(['post_id' => $this->post->id])
                    )
                    ->first();

                if (Arr::get($content->components, 'author') == $author) {
                    $userId = $this->post->created_by;
                } else {
                    $userId = $this->randomUserId();
                }
            } else {
                $userId = $this->randomUserId();
            }
        } else {
            $comment = $this->comment;
            $userId = $this->randomUserId();
        }

        $this->post->comments()->create(
            [
                'content' => $comment,
                'status' => Comment::STATUS_APPROVED,
                'object_id' => $this->post->id,
                'object_type' => $this->post->type,
                'user_id' => $userId,
            ]
        );

        event(new AddCommandToPostSuccess($this->post, $this->comment));
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->post->id))->releaseAfter(10)];
    }

    private function randomUserId(): ?int
    {
        return User::where(['is_fake' => true])
            ->where('id', '!=', $this->post->created_by)
            ->inRandomOrder()
            ->first()?->id;
    }
}
