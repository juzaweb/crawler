<?php

namespace Juzaweb\Crawler\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Juzaweb\Crawler\Helpers\Leech\LeechListItems;
use Juzaweb\Crawler\Http\Datatables\TemplateDatatable;
use Juzaweb\Crawler\Models\CrawlerTemplate;
use Illuminate\Http\Request;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\CMS\Models\User;
use Juzaweb\CMS\Traits\ResourceController;

class TemplateController extends BackendController
{
    use ResourceController {
        parseDataForSave as DataForSave;
        getDataForForm as DataForForm;
    }

    protected string $viewPrefix = 'crawler::template';

    /**
     * @param  array  $data
     * @param  CrawlerTemplate  $model
     */
    public function afterSave($data, $model, ...$params): void
    {
        $pages = Arr::get($data, 'pages', []);
        $pageIds = [];
        foreach ($pages as $page) {
            $p = $model->pages()->updateOrCreate(
                ['id' => $page['page_id']],
                $page
            );

            $pageIds[] = $p->id;
        }

        $model->pages()
            ->whereNotIn('id', $pageIds)
            ->delete();
    }

    protected function parseDataForSave(array $attributes, ...$params): array
    {
        $data = $this->DataForSave($attributes);
        if (empty($data['auto_leech'])) {
            $data['auto_leech'] = 0;
        }

        if (empty($data['user_id'])) {
            $data['user_id'] = User::first()->id;
        }

        return $data;
    }

    protected function getDataForForm($model, ...$params): array
    {
        $data = $this->DataForForm($model, ...$params);
        $data['linkPreview'] = action([static::class, 'preview']);
        return $data;
    }

    public function preview(Request $request): JsonResponse|RedirectResponse
    {
        $this->validate($request, [
            'pages.*.list_url' => 'required',
            'pages.*.element_item' => 'required',
        ]);

        $pages = $request->post('pages');
        $data = $pages[array_key_first($pages)];

        $element_item = $data['element_item'];
        $split = explode('|', $element_item);

        $element = $split[0];
        $attr = $split[1] ?? 'href';

        $list_url = $data['list_url'];
        $list = new LeechListItems($list_url, $element, $attr);
        $list->removeQueryString(1);

        return $this->success(
            [
                'items' => $list->getItems(),
            ]
        );
    }

    public function copy(Request $request): JsonResponse|RedirectResponse
    {
        $this->validate($request, [
            'ids' => 'required',
        ]);

        $ids = $request->post('ids', []);
        foreach ($ids as $id) {
            $template = CrawlerTemplate::find($id);
            $new_template = $template->replicate();
            $new_template->status = 2;
            $new_template->push();

            foreach ($template->components as $component) {
                $new_component = $component->replicate();
                $new_component->template_id = $new_template->id;
                $new_component->push();
            }

            foreach ($template->removes as $remove) {
                $new_remove = $remove->replicate();
                $new_remove->template_id = $new_template->id;
                $new_remove->push();
            }
        }

        return $this->success([
            'message' => 'Copy successful'
        ]);
    }

    protected function getDataTable(...$params): TemplateDatatable
    {
        return new TemplateDatatable();
    }

    protected function validator(array $attributes, ...$params): \Illuminate\Validation\Validator
    {
        return Validator::make(
            $attributes,
            [
                'name' => 'required',
                'post_status' => 'required',
                'user_id' => 'required|integer',
                'auto_leech' => 'nullable|in:0,1',
                'status' => 'required|in:active,inactive,test',
                'pages.*.element_item' => 'required',
                'pages.*.list_url' => 'required',
            ]
        );
    }

    protected function getModel(...$params): string
    {
        return CrawlerTemplate::class;
    }

    protected function getTitle(...$params): string
    {
        return trans('crawler::content.templates');
    }
}
