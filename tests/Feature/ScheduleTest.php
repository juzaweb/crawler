<?php

namespace Juzaweb\Modules\Crawler\Tests\Feature;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Juzaweb\Modules\Crawler\Tests\TestCase;

class ScheduleTest extends TestCase
{
    public function test_commands_are_scheduled()
    {
        $schedule = $this->app->make(Schedule::class);

        $events = collect($schedule->events());

        $crawlPagesEvent = $events->first(function (Event $event) {
            return str_contains($event->command, 'crawl:pages');
        });

        $crawlLinksEvent = $events->first(function (Event $event) {
            return str_contains($event->command, 'crawl:links');
        });

        $this->assertNotNull($crawlPagesEvent, 'crawl:pages command is not scheduled');
        $this->assertNotNull($crawlLinksEvent, 'crawl:links command is not scheduled');

        // Verify frequencies
        $this->assertEquals('*/10 * * * *', $crawlPagesEvent->expression);
        $this->assertEquals('* * * * *', $crawlLinksEvent->expression);
    }
}
