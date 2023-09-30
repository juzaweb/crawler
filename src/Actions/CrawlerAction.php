<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     The Anh Dang <dangtheanh16@gmail.com>
 * @link       https://juzaweb.com/cms
 * @license    MIT
 */

namespace Juzaweb\Crawler\Actions;

use Illuminate\Support\Str;
use Juzaweb\CMS\Abstracts\Action;
use Juzaweb\CMS\Facades\HookAction;
use Juzaweb\Crawler\Models\CrawlerTemplate;
use Juzaweb\Crawler\Support\Templates\DatabaseTemplate;
use Juzaweb\Crawler\Support\Templates\Xenforo;

class CrawlerAction extends Action
{
    public function handle(): void
    {
        $this->addAction(Action::BACKEND_INIT, [$this, 'addAddminMenu']);
        $this->addAction(Action::INIT_ACTION, [$this, 'registerCrawlerTemplates']);
        //$this->addAction(Action::BACKEND_CALL_ACTION, [$this, 'addScriptAdmin']);
    }

    public function registerCrawlerTemplates(): void
    {
        $this->hookAction->registerCrawlerTemplate(
            'xenforo',
            [
                'name' => 'Xenforo Forum',
                'class' => Xenforo::class,
            ]
        );

        $templates = CrawlerTemplate::get();

        foreach ($templates as $template) {
            $this->hookAction->registerCrawlerTemplate(
                $template->id,
                [
                    'name' => $template->name,
                    'class' => DatabaseTemplate::class,
                ]
            );
        }
    }

    public function addScriptAdmin(): void
    {
        $ver = app('plugins')->find('juzaweb/crawler')->getVersion();

        HookAction::enqueueScript(
            'crawler',
            'jw-styles/plugins/juzaweb/crawler/js/crawler.js',
            $ver
        );
    }

    public function addAddminMenu(): void
    {
        HookAction::registerAdminPage(
            'crawler',
            [
                'title' => trans('crawler::content.crawler'),
                'menu' => [
                    'position' => 30,
                ]
            ]
        );

        HookAction::registerAdminPage(
            'crawler.websites',
            [
                'title' => trans('crawler::content.websites'),
                'menu' => [
                    'position' => 1,
                    'parent' => 'crawler'
                ]
            ]
        );

        HookAction::registerAdminPage(
            'crawler.testing',
            [
                'title' => trans('crawler::content.testing'),
                'menu' => [
                    'position' => 20,
                    'parent' => 'crawler'
                ]
            ]
        );

        HookAction::registerAdminPage(
            'crawler.import-links',
            [
                'title' => trans('Import links'),
                'menu' => [
                    'position' => 50,
                    'parent' => 'crawler'
                ]
            ]
        );

        HookAction::registerAdminPage(
            'crawler.stats',
            [
                'title' => trans('crawler::content.analytics'),
                'menu' => [
                    'position' => 89,
                    'parent' => 'crawler'
                ]
            ]
        );
    }
}
