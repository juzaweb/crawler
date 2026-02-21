<?php

namespace Juzaweb\Modules\Crawler\Http\DataTables;

use Illuminate\Database\Eloquent\Model;
use Juzaweb\Modules\Core\DataTables\Action;
use Juzaweb\Modules\Core\DataTables\BulkAction;
use Juzaweb\Modules\Core\DataTables\Column;
use Juzaweb\Modules\Core\DataTables\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Juzaweb\Modules\Crawler\Models\CrawlerPage;

class CrawlerPagesDataTable extends DataTable
{
    protected string $actionUrl = '';
    protected string $sourceId;

    public function setSourceId(string $sourceId): static
    {
        $this->sourceId = $sourceId;
        $this->actionUrl = "crawler-sources/{$sourceId}/pages/bulk";
        return $this;
    }

    public function query(CrawlerPage $model): Builder
    {
        return $model->newQuery()->where('source_id', $this->sourceId);
    }

    public function getColumns(): array
    {
        return [
            Column::checkbox(),
            Column::id(),
            Column::actions(),
            Column::make('url'),
            Column::make('next_page'),
            Column::make('active'),
            Column::make('crawled_at'),
            Column::createdAt(),
        ];
    }

    public function actions(Model $model): array
    {
        return [
            Action::edit(admin_url("crawler-sources/{$this->sourceId}/pages/{$model->id}/edit"))
                ->can('crawler-pages.edit'),
            Action::delete()->can('crawler-pages.delete'),
        ];
    }

    public function bulkActions(): array
    {
        return [
            BulkAction::delete()->can('crawler-pages.delete'),
        ];
    }
}
