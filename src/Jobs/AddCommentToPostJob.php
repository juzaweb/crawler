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
use Illuminate\Queue\SerializesModels;
use Juzaweb\Backend\Models\Comment;
use Juzaweb\Backend\Models\Post;
use Juzaweb\CMS\Models\User;

class AddCommentToPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;

    public function __construct(protected Post $post, protected string $comment)
    {
    }

    public function handle(): void
    {
        $this->post->comments()->create(
            [
                'content' => $this->comment,
                'status' => Comment::STATUS_APPROVED,
                'object_id' => $this->post->id,
                'object_type' => $this->post->type,
                'user_id' => User::where(['is_fake' => true])
                    ->where('id', '!=', $this->post->created_by)
                    ->inRandomOrder()
                    ->first()?->id,
            ]
        );
    }
}
