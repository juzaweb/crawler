<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Juzaweb\Crawler\Events\PostSuccess;
use Juzaweb\Opp\Listeners\PublishPostOnSuccess;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PostSuccess::class => [
            PublishPostOnSuccess::class,
        ]
    ];
}
