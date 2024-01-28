<?php

namespace Juzaweb\Crawler\Actions;

use Juzaweb\CMS\Abstracts\Action;
use Juzaweb\Crawler\Models\CrawlerContent;

class ConfigAction extends Action
{
    /**
     * Execute the actions.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->addAction(Action::BACKEND_INIT, [$this, 'addAdminConfigs']);
        $this->addAction('post_types.form.left', [$this, 'addFormPost']);
    }

    public function addFormPost($model): void
    {
        $content = CrawlerContent::with(['link'])->where('post_id', $model->id)->first();
        if ($content) {
            echo "<a class='btn btn-primary' href=\"{$content->link->url}\" target='_blank' rel='noreferrer'>Original post</a>";
        }
    }

    public function addAdminConfigs(): void
    {
        $this->hookAction->registerSettingPage(
            'crawler',
            [
                'label' => trans('cms::app.setting'),
                'menu' => [
                    'parent' => 'crawler',
                    'position' => 99,
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
                'crawler_enable' => [
                    'type' => 'select',
                    'label' => 'Enable Crawler',
                    'form' => 'crawler',
                    'data' => [
                        'options' => [
                            0 => trans('cms::app.disabled'),
                            1 => trans('cms::app.enable'),
                        ],
                    ]
                ],
                'crawler_skip_origin_content' => [
                    'type' => 'select',
                    'label' => 'Skip origin content',
                    'form' => 'crawler',
                    'data' => [
                        'options' => [
                            0 => trans('cms::app.disabled'),
                            1 => trans('cms::app.enable')
                        ],
                    ]
                ],
                'crawler_enable_proxy' => [
                    'type' => 'select',
                    'label' => 'Enable Proxy',
                    'form' => 'crawler',
                    'data' => [
                        'options' => [
                            0 => trans('cms::app.disabled'),
                            1 => trans('cms::app.enable')
                        ],
                    ]
                ],
                'crawler_without_proxy' => [
                    'type' => 'select',
                    'label' => 'Crawl without proxy',
                    'form' => 'crawler',
                    'data' => [
                        'options' => [
                            1 => trans('cms::app.enable'),
                            0 => trans('cms::app.disabled'),
                        ],
                        'default' => 1,
                    ]
                ],
                'crawler_auto_publish_posts' => [
                    'type' => 'select',
                    'label' => 'Auto Publish Posts',
                    'form' => 'crawler',
                    'data' => [
                        'options' => [
                            0 => trans('cms::app.disabled'),
                            1 => trans('cms::app.enable'),
                        ],
                    ]
                ],
                'crawler_auto_publish_posts_per_day' => [
                    'type' => 'text',
                    'label' => 'Number of published posts per day',
                    'form' => 'crawler',
                ],
            ]
        );
    }
}
