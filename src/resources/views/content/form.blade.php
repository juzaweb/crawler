@extends('cms::layouts.backend')

@section('content')
    @component('cms::components.form_resource', [
        'model' => $model
    ])

        <div class="row">
            <div class="col-md-8">

                @foreach($model->components as $key => $component)
                    @php
                    $field = $key == 'content' ? 'editor': 'text';
                    @endphp

                    @if(is_array($component))
                        <div class="form-group">
                            <label class="col-form-label" for="ajnzul-link-id">{{ trans("cms::app.{$key}") }}</label>
                            <div>
                                <pre><code>{{ json_encode($component) }}</code></pre>
                            </div>
                        </div>

                    @else
                        {{ Field::{$field}(trans("cms::app.{$key}"), "components[{$key}]", ['value' => $component]) }}
                    @endif

                @endforeach

                {{ Field::text($model, 'is_source') }}

                {{ Field::text($model, 'lang') }}

                    <div class="form-group">
                        <label class="col-form-label" for="ajnzul-link-id">Link</label>
                        <div><a href="{{ $model->link->url }}" target="_blank" rel="noreferrer">{{ $model->link->url }}</a></div>
                    </div>

                {{--{{ Field::text($model, 'page_id') }}

                {{ Field::text($model, 'post_id') }}

                {{ Field::text($model, 'resource_id') }}

                {{ Field::text($model, 'website_id') }}--}}

            </div>

            <div class="col-md-4">

                {{ Field::text($model, 'status') }}

                @if($model->is_source)
                <button type="button" class="btn btn-warning re-get-content">
                    <i class="fa fa-refresh"></i> Re-Get
                </button>
                @else
                    <button type="button" class="btn btn-warning re-translate">
                        <i class="fa fa-language"></i> Re-Translate
                    </button>
                @endif
            </div>
        </div>

    @endcomponent

    <script type="text/javascript">
        $(document).on('click', '.re-get-content', function (e) {
            let btn = $(this);
            let icon = btn.find('i').attr('class');

            btn.find('i').attr('class', 'fa fa-spinner fa-spin');
            btn.prop("disabled", true);

            $.ajax({
                type: 'POST',
                url: '{{ route('crawler.websites.contents.re-get', [$model->website_id, $model->id]) }}',
                dataType: 'json',
                data: {}
            }).done(function(response) {

                btn.find('i').attr('class', icon);
                btn.prop("disabled", false);

                if (response.status === false) {
                    show_message(response);
                    return false;
                }

                show_message(response);

                setTimeout(function () {
                    window.location = "";
                }, 300);

                return false;
            }).fail(function(response) {
                btn.find('i').attr('class', icon);
                btn.prop("disabled", false);

                show_message(response);
                return false;
            });
        });

        $(document).on('click', '.re-translate', function (e) {
            let btn = $(this);
            let icon = btn.find('i').attr('class');

            btn.find('i').attr('class', 'fa fa-spinner fa-spin');
            btn.prop("disabled", true);

            $.ajax({
                type: 'POST',
                url: '{{ route('crawler.websites.contents.re-translate', [$model->website_id, $model->id]) }}',
                dataType: 'json',
                data: {}
            }).done(function(response) {

                btn.find('i').attr('class', icon);
                btn.prop("disabled", false);

                if (response.status === false) {
                    show_message(response);
                    return false;
                }

                show_message(response);

                setTimeout(function () {
                    window.location = "";
                }, 300);

                return false;
            }).fail(function(response) {
                btn.find('i').attr('class', icon);
                btn.prop("disabled", false);

                show_message(response);
                return false;
            });
        });
    </script>
@endsection
