{{ Field::text(trans('crawler::content.list_url'), "pages[{$marker}][url]", ['class' => 'page-list_url', 'value' => $model->url ?? '']) }}

{{ Field::text(trans('crawler::content.list_url_page'), "pages[{$marker}][url_with_page]", ['value' => $model->url_with_page ?? '']) }}

{{ Field::select(trans('crawler::content.post_type'), "pages[{$marker}][post_type]", [
    'options' => $types->mapWithKeys(
        function ($item) {
            return [
                $item['key'] => $item['label'],
            ];
        }
    ),
    'value' => $model->post_type ?? null,
    'default' => 'posts'
]) }}

{{ Field::checkbox(trans('cms::app.active'), "pages[{$marker}][active]", ['checked' => ($model->active ?? 1) == 1]) }}

{{ Field::select(
    trans('cms::app.language'),
    "pages[{$marker}][lang]",
    [
        'options' => collect($languages)->mapWithKeys(fn($item) => [$item['code'] => $item['name']]),
        'default' => 'en',
    ]
) }}
