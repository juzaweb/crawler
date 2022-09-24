<?php

namespace Juzaweb\Crawler\Http\Datatables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Juzaweb\CMS\Abstracts\DataTable;
use Juzaweb\Crawler\Models\CrawlerWebsite;

class WebsiteDatatable extends DataTable
{
    public function columns(): array
    {
        return [
            'domain' => [
                'label' => trans('crawler::content.website'),
                'formatter' => [$this, 'rowActionsFormatter']
            ],
            'template_class' => [
                'label' => trans('crawler::content.template'),
                'width' => '15%',
                'formatter' => function ($value, $row, $index) {
                    return class_basename($value);
                }
            ],
            'has_ssl' => [
                'label' => trans('crawler::content.has_ssl'),
                'width' => '10%',
                'align' => 'center',
                'formatter' => function ($value, $row, $index) {
                    return \Field::checkbox(
                        '',
                        'toggle_has_ssl',
                        [
                            'checked' => $value == 1,
                        ]
                    )->render();
                }
            ],
            'active' => [
                'label' => trans('cms::app.status'),
                'width' => '10%',
                'align' => 'center',
                'formatter' => function ($value, $row, $index) {
                    return \Field::checkbox(
                        '',
                        'toggle_active',
                        [
                            'checked' => $value == 1,
                        ]
                    )->render();
                }
            ],
            'created_at' => [
                'label' => trans('cms::app.created_at'),
                'width' => '20%',
                'align' => 'center',
                'formatter' => function ($value, $row, $index) {
                    return jw_date_format($row->created_at);
                }
            ]
        ];
    }

    public function query($data): Builder
    {
        $query = CrawlerWebsite::query();

        if ($keyword = Arr::get($data, 'keyword')) {
            $query->where(
                function (Builder $builder) use ($keyword) {
                    $builder->orWhere(
                        'url',
                        'ilike',
                        '%'. $keyword .'%'
                    );
                    $builder->orWhere(
                        'error',
                        'ilike',
                        '%'. $keyword .'%'
                    );
                }
            );
        }

        if ($status = Arr::get($data, 'status')) {
            $query->where('status', '=', $status);
        }

        return $query;
    }

    public function searchFields(): array
    {
        return [
            'keyword' => [
                'type' => 'text',
                'label' => trans('cms::app.keyword'),
                'placeholder' => trans('cms::app.keyword'),
            ],
            'active' => [
                'type' => 'select',
                'label' => trans('cms::app.status'),
                'options' => [
                    '' => trans('cms::app.active'),
                    0 => trans('cms::app.active'),
                    1 => trans('cms::app.unactive'),
                ],
            ],
        ];
    }

    public function bulkActions($action, $ids)
    {
        switch ($action) {
            case 'delete':
                CrawlerWebsite::destroy($ids);
                break;
        }
    }
}
