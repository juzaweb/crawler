<?php

namespace Juzaweb\Modules\Crawler\Http\DataTables;

use Illuminate\Database\Eloquent\Model;
use Juzaweb\Modules\Core\DataTables\Action;
use Juzaweb\Modules\Core\DataTables\BulkAction;
use Juzaweb\Modules\Core\DataTables\Column;
use Juzaweb\Modules\Core\DataTables\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;
use Yajra\DataTables\EloquentDataTable;

class CrawlerSourcesDataTable extends DataTable
{
    protected string $actionUrl = 'crawler-sources/bulk';

    protected array $rawColumns = ['pages_count', 'actions', 'checkbox'];

    public function query(CrawlerSource $model): Builder
    {
        return $model->newQuery()->withCount('pages');
    }

    public function getColumns(): array
    {
        return [
            Column::checkbox(),
            Column::id(),
            Column::actions(),
            Column::editLink('name', 'crawler-sources/{id}/edit', __('Name')),
            Column::make('pages_count')->title(__('Pages'))->width('10%')->addClass('text-center')->orderable(false)->searchable(false),
            Column::make('active'),
            Column::make('data_type'),
            Column::createdAt(),
        ];
    }

    public function renderColumns(EloquentDataTable $builder): EloquentDataTable
    {
        return $builder->editColumn('pages_count', function ($row) {
            return '<a rel="nofollow noopener noreferrer" href="'. admin_url("crawler-sources/{$row->id}/pages") .'" class="btn btn-info btn-sm"><i class="fas fa-list-alt"></i> '. $row->pages_count .'</a>';
        });
    }

    public function actions(Model $model): array
    {
        return [
            Action::edit(admin_url("crawler-sources/{$model->id}/edit"))->can('crawler-sources.edit'),
            Action::delete()->can('crawler-sources.delete'),
        ];
    }

    public function bulkActions(): array
    {
        return [
            BulkAction::delete()->can('crawler-sources.delete'),
            BulkAction::make(__('Activate'))
                ->action('activate')
                ->icon('fa fa-check')
                ->can('crawler-sources.edit'),
            BulkAction::make(__('Deactivate'))
                ->action('deactivate')
                ->icon('fa fa-times')
                ->can('crawler-sources.edit'),
        ];
    }
}
