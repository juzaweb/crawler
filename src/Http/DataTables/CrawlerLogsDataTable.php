<?php

namespace Juzaweb\Modules\Crawler\Http\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Juzaweb\Modules\Core\DataTables\Action;
use Juzaweb\Modules\Core\DataTables\BulkAction;
use Juzaweb\Modules\Core\DataTables\Column;
use Juzaweb\Modules\Core\DataTables\DataTable;
use Juzaweb\Modules\Crawler\Enums\CrawlerLogStatus;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;
use Yajra\DataTables\EloquentDataTable;

class CrawlerLogsDataTable extends DataTable
{
    protected string $actionUrl = 'crawler-logs/bulk';

    protected array $rawColumns = ['status', 'url', 'actions', 'checkbox'];

    public function query(CrawlerLog $model): Builder
    {
        return $model->newQuery()->with(['source', 'post']);
    }

    public function getColumns(): array
    {
        return [
            Column::checkbox(),
            Column::id(),
            Column::actions(),
            Column::make('url'),
            Column::make('source.name'),
            Column::make('post'),
            Column::make('status'),
            Column::createdAt(),
        ];
    }

    public function actions(Model $model): array
    {
        return [
            Action::edit(admin_url("crawler-logs/{$model->id}/edit"))->can('crawler-logs.edit'),
            Action::delete()->can('crawler-logs.delete'),
        ];
    }

    public function bulkActions(): array
    {
        return [
            BulkAction::delete()->can('crawler-logs.delete'),
            BulkAction::make(__('Retry'))
                ->action('retry')
                ->icon('fas fa-redo')
                ->can('crawler-logs.edit'),
        ];
    }

    public function renderColumns(EloquentDataTable $builder): EloquentDataTable
    {
        $dataTable = parent::renderColumns($builder);

        $dataTable->editColumn('status', function ($row) {
            $content = '<span class="badge badge-' . $row->status->color();

            if (in_array($row->status, [CrawlerLogStatus::FAILED, CrawlerLogStatus::FAILED_POSTING])) {
                $content .= ' show-log-error" style="cursor: pointer" data-error="' . e(json_encode($row->error));
            }

            $content .= '">' . $row->status->label() . '</span>';

            return $content;
        });

        $dataTable->editColumn('url', function ($row) {
            return '<a href="' . $row->url . '" target="_blank">' . $row->url . '</a>';
        });

        $dataTable->addColumn('post', function ($row) {
            return $row->post?->title ?? $row->post?->name ?? '';
        });

        return $dataTable;
    }
}
