@extends('cms::layouts.backend')

@section('content')
    @component('cms::components.form_resource', [
        'model' => $model
    ])

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">

                        {{ Field::text($model, 'domain', [
                            'required' => true
                        ]) }}

                        {{ Field::checkbox($model, 'has_ssl', [
                            'checked' => (bool) ($model->has_ssl ?? true)
                        ]) }}

                    </div>
                </div>

            </div>

            <div class="col-md-4">
                @php
                $templateOptions = $templates->mapWithKeys(
                    function ($item) {
                        return [
                            $item['class'] => $item['name'],
                        ];
                    }
                );
                @endphp

                {{ Field::select($model, 'template_class', ['options' => $templateOptions])}}
            </div>
        </div>

    @endcomponent
@endsection
