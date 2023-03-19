<?php

namespace Juzaweb\Crawler\Actions;

use Juzaweb\CMS\Abstracts\Action;

class ConfigAction extends Action
{
    /**
     * Execute the actions.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->addAction(Action::BACKEND_INIT, [$this, 'addAdminConfig']);
    }

    public function addAdminConfig()
    {
        $this->hookAction->registerSettingPage(
            'crawler',
            [
                'label' => trans('cms::app.setting'),
                'menu' => [
                    'parent' => 'crawler',
                ]
            ]
        );

        $this->hookAction->addSettingForm(
            'crawler',
            [
                'name' => 'Crawler Settings',
                'page' => 'crawler'
            ]
        );

        $this->hookAction->registerConfig(
            [
                'crawler_skip_origin_content' => [
                    'type' => 'select',
                    'label' => 'Skip origin content',
                    'form' => 'crawler',
                    'data' => [
                        'options' => [0 => trans('cms::app.disabled'), 1 => trans('cms::app.enable')],
                    ]
                ],
                'crawler_enable_translate' => [
                    'type' => 'select',
                    'label' => 'Enable Translate',
                    'form' => 'crawler',
                    'data' => [
                        'options' => [0 => trans('cms::app.disabled'), 1 => trans('cms::app.enable')],
                    ]
                ],
                'crawler_translate_languages' => [
                    'type' => 'select',
                    'label' => 'Translate languages',
                    'form' => 'crawler',
                    'data' => [
                        'multiple' => true,
                        'options' => collect(config('locales'))
                            ->mapWithKeys(fn($item) => [$item['code'] => $item['name']])
                            ->toArray(),
                    ]
                ]
            ]
        );
    }
}
