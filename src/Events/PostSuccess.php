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
use Juzaweb\Crawler\Models\CrawlerContent;

class PostSuccess
{
    public function __construct(public Post $post, public CrawlerContent $content)
    {
    }
}
