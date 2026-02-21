<?php

namespace Juzaweb\Modules\Crawler\Http\DataTables;

use Illuminate\Database\Eloquent\Model;
use Juzaweb\Modules\Core\DataTables\Action;
use Juzaweb\Modules\Core\DataTables\BulkAction;
use Juzaweb\Modules\Core\DataTables\Column;
use Juzaweb\Modules\Core\DataTables\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Juzaweb\Modules\Crawler\Models\CrawlerSource;

class CrawlerSourcesDataTable extends DataTable
{
    protected string $actionUrl = 'crawler-sources/bulk';

    public function query(CrawlerSource $model): Builder
    {
        return $model->newQuery();
    }

    public function getColumns(): array
    {
        return [
            Column::checkbox(),
            Column::id(),
            Column::actions(),
            Column::make('name'),
            Column::make('active'),
            Column::make('data_type'),
            Column::createdAt(),
        ];
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
        ];
    }
}
