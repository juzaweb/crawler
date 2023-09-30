@extends('cms::layouts.backend')

@section('content')
    @component('cms::components.form_resource', [
        'model' => $model
    ])

        <div class="row">
            <div class="col-md-8">
                {{ Field::text($model, 'domain', [
                    'required' => true
                ]) }}

                {{ Field::checkbox($model, 'has_ssl', [
                    'checked' => (bool) ($model->has_ssl ?? true)
                ]) }}


                <div class="row mt-3">
                    <div class="col-md-6"></div>
                    <div class="col-md-6 text-right">
                        <a href="javascript:void(0)"
                           class="btn btn-success btn-sm"
                           id="add-new-replace"
                        >
                            Add Replace
                        </a>
                    </div>

                    <div class="col-md-12 mt-2">
                        <table class="table" id="table-replaces">
                            <thead>
                                <tr>
                                    <th>Search</th>
                                    <th style="width: 50%;text-align: center">Replace</th>
                                    <th style="width: 15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($model->translate_replaces ?? [] as $index => $replace)
                                @component('crawler::website.components.replace_item', [
                                    'marker' => $index,
                                    'model' => $model,
                                    'item' => $replace,
                                ])

                                @endcomponent
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                {{ Field::checkbox($model, 'active', [
                    'checked' => (bool) ($model->active ?? true)
                ]) }}

                {{ Field::select($model, 'template_class', ['options' => $templateOptions]) }}
            </div>
        </div>
    @endcomponent


    <template id="replace-item-template">
        @component('crawler::website.components.replace_item', [
                'marker' => '{marker}',
            ])

        @endcomponent
    </template>

    <script type="text/javascript">
        const tableReplaceEl = $('#table-replaces');

        $('#add-new-replace').on('click', function () {
            let temp = document.getElementById('replace-item-template').innerHTML;
            let marker = (new Date()).getTime();
            temp = replace_template(temp, {marker: marker});
            $('#table-replaces tbody').prepend(temp);
        });

        tableReplaceEl.on('click', '.remove-replace-item', function () {
            $(this).closest('tr').remove();
        });
    </script>

@endsection
