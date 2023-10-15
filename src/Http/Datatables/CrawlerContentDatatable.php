<?php

namespace Juzaweb\Crawler\Http\Datatables;

use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Juzaweb\CMS\Abstracts\DataTable;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerContent;

class CrawlerContentDatatable extends DataTable
{
    protected int $websiteId;

    public function mount($websiteId)
    {
        $this->websiteId = $websiteId;
    }

    /**
     * Columns datatable
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            'title' => [
                'label' => trans('cms::app.title'),
                'formatter' => fn($value, $row, $index)
                => '<a href="'. route('admin.crawler.websites.contents.edit', [$row->website_id, $row->id]) .'">'
                    .($row->components['title'] ?? 'N/A')
                    .'</a>',
            ],
            'lang' => [
                'label' => trans('crawler::content.lang'),
            ],
            'link_id' => [
                'label' => trans('crawler::content.link'),
                'width' => '15%',
                'formatter' => fn($value, $row, $index)
                => '<a href="'.$row->link->url.'" target="_blank" rel="noreferrer">'.$value.'</a>',
            ],
            'page_id' => [
                'label' => trans('crawler::content.page'),
            ],
            'post_id' => [
                'label' => trans('crawler::content.post'),
            ],
            'status' => [
                'label' => trans('crawler::content.status'),
            ],
            'updated_at' => [
                'label' => trans('cms::app.updated_at'),
                'width' => '15%',
                'align' => 'center',
                'formatter' => function ($value, $row, $index) {
                    return jw_date_format($row->updated_at);
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

    /**
     * Query data datatable
     *
     * @param  array  $data
     * @return Builder
     */
    public function query(array $data): Builder
    {
        $query = CrawlerContent::where(['website_id' => $this->websiteId]);
        $source = Arr::get($data, 'is_source');

        if ($keyword = Arr::get($data, 'keyword')) {
            $query->where(
                function (Builder $q) use ($keyword) {
                    $q->whereJsonContains('components->title', $keyword);
                }
            );
        }

        if ($source !== null) {
            $query->where('is_source', '=', (bool) $source);
        }

        if ($status = Arr::get($data, 'status')) {
            $query->where('status', '=', $status);
        }

        return $query;
    }

    public function actions(): array
    {
        $actions = [];
        $actions['reget'] = 'Re-get Content';
        $actions['retrans'] = 'Re-translate Content';
        $actions['repost'] = 'Re-post';
        return array_merge($actions, parent::actions());
    }

    public function bulkActions($action, $ids): void
    {
        switch ($action) {
            case 'delete':
                CrawlerContent::destroy($ids);
                break;
            case 'reget':
                $contents = CrawlerContent::with(['link'])
                    ->whereIn('id', $ids)
                    ->where(['is_source' => true])
                    ->get();

                foreach ($contents as $content) {
                    /** @var CrawlerContent $content */
                    $content->reget();
                }

                break;
            case 'retrans':
                $contents = CrawlerContent::with(['link'])
                    ->whereIn('id', $ids)
                    ->where(['is_source' => false])
                    ->get();

                foreach ($contents as $content) {
                    /** @var CrawlerContent $content */
                    $content->retrans();
                }

                break;
            case 'repost':
                $contents = CrawlerContent::with(['link'])
                    ->whereIn('id', $ids)
                    ->where(['is_source' => false])
                    ->get();
                $crawler = app(CrawlerContract::class);

                foreach ($contents as $content) {
                    /** @var CrawlerContent $content */
                    $content->update(['status' => CrawlerContent::STATUS_POSTTING]);

                    DB::beginTransaction();
                    try {
                        $crawler->savePost($content);

                        DB::commit();
                    } catch (Exception $e) {
                        DB::rollBack();
                        report($e);
                        $content->update(
                            [
                                'status' => CrawlerContent::STATUS_ERROR,
                            ]
                        );
                    }
                }

                break;
        }
    }

    public function searchFields(): array
    {
        return [
            'keyword' => [
                'type' => 'text',
                'label' => trans('cms::app.keyword'),
                'placeholder' => trans('cms::app.keyword'),
            ],
            'is_source' => [
                'type' => 'select',
                'label' => 'Source content',
                'options' => [
                    0 => 'No',
                    1 => 'Yes',
                ]
            ],
            'status' => [
                'type' => 'select',
                'label' => 'Status',
                'options' => CrawlerContent::statuses(),
            ],
        ];
    }
}
