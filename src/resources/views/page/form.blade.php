@extends('cms::layouts.backend')

@section('content')
    @component('cms::components.form_resource', [
        'model' => $model
    ])

        <div class="row">
            <div class="col-md-8">

                {{ Field::text($model, "url", ['label' => trans('crawler::content.list_url')]) }}

                {{ Field::text($model, "url_with_page", ['label' => trans('crawler::content.list_url_page')]) }}

                {{ Field::select($model, "post_type", [
                    'options' => $types->mapWithKeys(
                        function ($item) {
                            return [
                                $item['key'] => $item['label'],
                            ];
                        }
                    ),
                    'lable' => trans('crawler::content.post_type'),
                    'default' => 'posts',
                ]) }}

                {{ Field::checkbox($model, "active", ['checked' => ($model->active ?? 1) == 1, 'lable' => trans('cms::app.active')]) }}

                {{ Field::select(
                    $model,
                    "lang",
                    [
                        'options' => collect($languages)->mapWithKeys(fn($item) => [$item['code'] => $item['name']]),
                        'default' => 'en',
                        'lable' => trans('cms::app.language'),
                    ]
                ) }}

                {{ Field::text($model, "max_page", ['label' => 'Max page', 'disabled' => (bool) $model->id]) }}

            </div>

            <div class="col-md-4">
                @component('crawler::page.components.taxonomies', ['taxonomies' => $taxonomies, 'model' => $model])

                @endcomponent
            </div>
        </div>

    @endcomponent
@endsection
