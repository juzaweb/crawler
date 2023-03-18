<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     The Anh Dang <dangtheanh16@gmail.com>
 * @link       https://juzaweb.com/cms
 * @license    MIT
 */

namespace Juzaweb\Crawler;

use Juzaweb\CMS\Abstracts\Action;
use Juzaweb\CMS\Facades\HookAction;
use Juzaweb\Crawler\Support\Templates\MediumCom;
use Juzaweb\Crawler\Support\Templates\TruyenFullVN;

class CrawlerAction extends Action
{
    public function handle()
    {
        $this->addAction(Action::BACKEND_INIT, [$this, 'addAddminMenu']);
        $this->addAction(Action::INIT_ACTION, [$this, 'registerCrawlerTemplates']);
        //$this->addAction(Action::BACKEND_CALL_ACTION, [$this, 'addScriptAdmin']);
    }

    public function registerCrawlerTemplates()
    {
        $args = [
            'name' => 'TruyenFull.vn',
            'class' => TruyenFullVN::class,
        ];

        $this->hookAction->registerCrawlerTemplate(
            'truyenfullvn',
            $args
        );

        $this->hookAction->registerCrawlerTemplate(
            'medium',
            [
                'name' => 'Medium.com',
                'class' => MediumCom::class,
            ]
        );
    }

    public function addScriptAdmin()
    {
        $ver = app('plugins')->find('juzaweb/crawler')->getVersion();

        HookAction::enqueueScript(
            'crawler',
            'jw-styles/plugins/juzaweb/crawler/js/crawler.js',
            $ver
        );
    }

    public function addAddminMenu()
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
    }
}
