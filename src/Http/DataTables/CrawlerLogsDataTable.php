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
use Juzaweb\Modules\Crawler\Models\CrawlerPage;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;

class CrawlerLogsDataTable extends DataTable
{
    protected string $actionUrl = 'crawler-logs/bulk';

    public function query(CrawlerLog $model): Builder
    {
        return $model->newQuery()->with(['source']);
    }

    public function searchFields(): array
    {
        return [
            'keyword' => [
                'type' => 'text',
                'label' => trans('crawler::app.keyword'),
                'placeholder' => trans('crawler::app.keyword'),
            ],
            'source_id' => [
                'type' => 'select',
                'width' => '100px',
                'label' => trans('crawler::app.source'),
                'options' => CrawlerSource::pluck('name', 'id')->toArray(),
            ],
            'page_id' => [
                'type' => 'select',
                'width' => '100px',
                'label' => trans('crawler::app.page'),
                'options' => CrawlerPage::pluck('url', 'id')->toArray(),
            ],
            'status' => [
                'type' => 'select',
                'width' => '100px',
                'label' => trans('crawler::app.status'),
                'options' => collect(CrawlerLogStatus::cases())->mapWithKeys(fn($item) => [$item->value => strtoupper($item->value)])->toArray(),
            ],
        ];
    }

    public function getColumns(): array
    {
        return [
            Column::checkbox(),
            Column::id(),
            Column::actions(),
            Column::make('url'),
            Column::make('source.name'),
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
        ];
    }
}
