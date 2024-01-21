<?php

namespace Juzaweb\Crawler\Http\Datatables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Juzaweb\Backend\Models\Taxonomy;
use Juzaweb\CMS\Abstracts\DataTable;
use Juzaweb\Crawler\Models\CrawlerPage;
use Juzaweb\Crawler\Models\CrawlerWebsite;

class CrawlerPageDatatable extends DataTable
{
    protected CrawlerWebsite $website;

    public function mount(int $webId)
    {
        $this->website = CrawlerWebsite::find($webId);
    }

    public function columns(): array
    {
        return [
            'url' => [
                'label' => trans('crawler::content.url'),
                'formatter' => [$this, 'rowActionsFormatter'],
            ],
            'next_page' => [
                'label' => trans('crawler::content.next_page'),
                'width' => '5%',
                'align' => 'center',
            ],
            'post_type' => [
                'label' => trans('crawler::content.post_type'),
                'width' => '10%',
            ],
            'taxonomies' => [
                'label' => trans('crawler::content.taxonomies'),
                'width' => '15%',
                'sortable' => false,
                'formatter' => function ($value, $row, $index) {
                    return Taxonomy::whereIn('id', $row->category_ids['categories'] ?? [])
                        ->orderBy('level', 'ASC')
                        ->get(['name'])
                        ->pluck('name')
                        ->join(' » ');
                },
            ],
            'active' => [
                'label' => trans('cms::app.active'),
                'width' => '10%',
                'align' => 'center',
                'formatter' => function ($value, $row, $index) {
                    return \Field::checkbox(
                        '',
                        'toggle_active',
                        [
                            'checked' => $value == 1,
                            'disabled' => true,
                        ]
                    )->render();
                }
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

    public function query(array $data): Builder
    {
        $query = CrawlerPage::where(['website_id' => $this->website->id]);

        if ($keyword = Arr::get($data, 'keyword')) {
            $query->where(
                function (Builder $q) use ($keyword) {
                    $q->where('url', JW_SQL_LIKE, '%'. $keyword .'%');
                }
            );
        }

        return $query;
    }

    public function bulkActions($action, $ids): void
    {
        switch ($action) {
            case 'delete':
                CrawlerPage::destroy($ids);
                break;
        }
    }
}
