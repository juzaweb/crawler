<?php

namespace Juzaweb\Modules\Crawler\Http\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Juzaweb\Modules\Core\DataTables\Action;
use Juzaweb\Modules\Core\DataTables\BulkAction;
use Juzaweb\Modules\Core\DataTables\Column;
use Juzaweb\Modules\Core\DataTables\DataTable;
use Juzaweb\Modules\Core\DataTables\HtmlBuilder;
use Juzaweb\Modules\Crawler\Enums\CrawlerLogStatus;
use Juzaweb\Modules\Crawler\Models\CrawlerLog;
use Juzaweb\Modules\Crawler\Models\CrawlerPage;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;

class CrawlerLogsDataTable extends DataTable
{
    protected string $actionUrl = 'crawler-logs/bulk';

    public function query(CrawlerLog $model): Builder
    {
        $query = $model->newQuery()->with(['source']);

        if ($sourceId = request()->get('source_id')) {
            $query->where('source_id', $sourceId);
        }

        if ($pageId = request()->get('page_id')) {
            $query->where('page_id', $pageId);
        }

        if ($status = request()->get('status')) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->dom($this->dom)
            ->addTableClass($this->tableClass)
            ->setTableId($this->id)
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy($this->orderBy)
            ->selectStyleSingle()
            ->actionUrl($this->getActionUrl())
            ->bulkActions($this->bulkActions())
            ->ajax([
                'url' => $this->getActionUrl(),
                'data' => 'function(d) {
                    d.source_id = $("select[name=source_id]").val();
                    d.page_id = $("select[name=page_id]").val();
                    d.status = $("select[name=status]").val();
                }',
            ]);
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
