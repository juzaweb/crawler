@extends('cms::layouts.backend')

@section('content')
    @component('cms::components.form_resource', [
        'model' => $model
    ])

        <div class="row">
            <div class="col-md-12">

            {{ Field::text($model, 'url') }}

			{{ Field::text($model, 'url_with_page') }}

			{{ Field::text($model, 'post_type') }}

			{{ Field::text($model, 'active') }}

            </div>
        </div>

    @endcomponent
@endsection
