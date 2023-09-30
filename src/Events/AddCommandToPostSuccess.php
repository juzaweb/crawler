<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Events;

use Juzaweb\Backend\Models\Post;

class AddCommandToPostSuccess
{
    public function __construct(protected Post $post, protected string|array $comment)
    {
    }
}
