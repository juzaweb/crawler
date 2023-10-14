@extends('cms::layouts.backend')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <form class="form-ajax"
                  method="post"
                  action="{{ route('crawler.import-pages.find') }}"
                  data-success="show_results"
            >

                @php
                    /** @var \Illuminate\Support\Collection $websites */
                    $websiteOptions[''] = '-------';

                    $websiteOptions = $websites->mapWithKeys(
                        fn($item) => [$item['id'] => $item['domain']]
                    )->all();
                @endphp

                {{ Field::select('Website', 'website', [
                    'options' => $websiteOptions,
                    'value' => $importData['website'] ?? null,
                    ]
                ) }}

                {{ Field::text('URL', 'url', ['required' => true, 'value' => $importData['url'] ?? null]) }}

                {{ Field::text('Search Regex Url', 'search', ['required' => true, 'value' => $importData['search'] ?? null]) }}

                <button type="submit" class="btn btn-success" data-loading-text="Loading...">Find</button>

            </form>
        </div>

        <div class="col-md-8">
            <form action="" method="post" class="form-ajax" data-success="import_success">
                <input type="hidden" name="website" id="import-website" value="">
                <ul id="results" class="list-group"></ul>

                <div class="box-hidden mt-3 mb-5" id="import-button">
                    <button type="submit" class="btn btn-primary" data-loading-text="Loading...">Import</button>
                </div>
            </form>
        </div>
    </div>


    <template id="page-template">
        <li>
            <div class="row">
                <div class="col-md-3">
                    <a href="{href}" target="_blank" rel="noreferrer">{text}</a>
                </div>

                <div class="col-md-4">
                    <select name="categories[]"
                            class="form-control load-taxonomies"
                            data-taxonomy="categories"
                    >
                        <option value="">----</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <input type="text" class="form-control" name="max_page[]" value="" autocomplete="off">
                </div>

                <div class="col-md-2">
                    <select name="active[]" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <input type="hidden" class="form-control" name="href[]" value="{href}">

            </div>
        </li>
    </template>

    <script type="text/javascript">
        function show_results(form, response) {
            $('#results').empty();
            $('#import-button').hide('slow');

            if (response.data?.links) {
                let template = $('#page-template').html();

                $.each(response.data.links, function (index, row) {
                    $('#results').append(replace_template(template, row));
                });

                initSelect2('#results');
                $('#import-website').val(form.find('select[name="website"]').val());
                $('#import-button').show('slow');
            }

            $('.jw-message').remove();
        }

        function import_success(form, response) {
            $('#results').empty();
            $('#import-button').hide('slow');
            show_message(response);
        }

        $('select[name="website"]').on('change', function () {
            $.ajax({
                type: 'GET',
                url: '{{ admin_url('crawler/import-pages/website-info') }}',
                dataType: 'json',
                data: {
                    website: $(this).val(),
                },
                success: (function(response) {
                    if (response.data.website) {
                        $('input[name="url"]').val(`https://`+response.data.website.domain);
                    }

                    if (response.data.template.page_regex) {
                        $('input[name="search"]').val(response.data.template.page_regex);
                    }

                    return false;
                })
            }).fail(function(response) {
                show_message(response);
                return false;
            });
        });
    </script>
@endsection
