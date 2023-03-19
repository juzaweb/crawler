<?php

namespace Juzaweb\Crawler\Http\Datatables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Juzaweb\CMS\Abstracts\DataTable;
use Juzaweb\Crawler\Models\CrawlerPage;

class CrawlerPageDatatable extends DataTable
{
    /**
     * Columns datatable
     *
     * @return array
     */
    public function columns()
    {
        return [
            // 'title' => [
            //     'label' => trans('cms::app.title'),
            //     'formatter' => [$this, 'rowActionsFormatter'],
            // ],
            'url' => [
                'label' => trans('crawler::content.url'),
            ],
            'url_with_page' => [
                'label' => trans('crawler::content.url_with_page'),
            ],
            'post_type' => [
                'label' => trans('crawler::content.post_type'),
            ],
            'is_resource_page' => [
                'label' => trans('crawler::content.is_resource_page'),
            ],
            'active' => [
                'label' => trans('crawler::content.active'),
            ],
            'created_at' => [
                'label' => trans('cms::app.created_at'),
                'width' => '15%',
                'align' => 'center',
                'formatter' => function ($value, $row, $index) {
                    return jw_date_format($row->created_at);
                }
            ]
        ];
    }

    /**
     * Query data datatable
     *
     * @param array $data
     * @return Builder
     */
    public function query($data)
    {
        $query = CrawlerPage::query();

        if ($keyword = Arr::get($data, 'keyword')) {
            $query->where(
                function (Builder $q) use ($keyword) {
                    // $q->where('title', JW_SQL_LIKE, '%'. $keyword .'%');
                }
            );
        }

        return $query;
    }

    public function bulkActions($action, $ids)
    {
        switch ($action) {
            case 'delete':
                CrawlerPage::destroy($ids);
                break;
        }
    }
}
