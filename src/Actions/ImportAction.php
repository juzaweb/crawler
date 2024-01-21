<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Actions;

use Illuminate\Support\Collection;
use Juzaweb\CMS\Abstracts\Action;
use Juzaweb\CMS\Facades\HookAction;
use Juzaweb\Crawler\Http\Controllers\ImportController;

class ImportAction extends Action
{
    public function handle(): void
    {
        $this->addAction('post_type.post-types.btn_group', [$this, 'addImportBtns']);
        $this->addAction('post_type.post-types.index', [$this, 'addImportModal']);
        $this->addAction(Action::BACKEND_INIT, [$this, 'addImportAjax']);
    }

    public function addImportBtns(Collection $setting): void
    {
        if ($setting->get('key') === 'pages') {
            return;
        }

        echo '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#crawler-import-modal">
<i class="fa fa-download"></i> '. trans('cms::app.import') .'</button>';
    }

    public function addImportModal(Collection $setting): void
    {
        if ($setting->get('key') === 'pages') {
            return;
        }

        $templateOptions = HookAction::getCrawlerTemplates()->mapWithKeys(
            function ($item, $key) {
                if (is_numeric($key)) {
                    return [$key => $item['name']];
                }

                return [
                    $item['class'] => $item['name']
                ];
            }
        );

        echo e(view('crawler::components.import-modal', compact('templateOptions', 'setting')));
    }

    public function addImportAjax(): void
    {
        $this->registerAdminAjax(
            'crawler-import',
            [
                'method' => 'POST',
                'callback' => [ImportController::class, 'import'],
            ]
        );
    }
}
