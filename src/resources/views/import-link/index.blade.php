@extends('cms::layouts.backend')

@section('content')
    <form class="form-ajax" method="post" data-success="show_results">
        <div class="row">
            <div class="col-md-4">
                @php
                    $pageOptions = $pages->mapWithKeys(fn($item) => [$item['id'] => $item['url']]);
                @endphp

                {{ Field::select('Page', 'page', ['options' => $pageOptions, 'value' => $importData['page'] ?? null]) }}

                {{ Field::text('URL', 'url', ['required' => true, 'value' => $importData['url'] ?? null]) }}

                {{ Field::text('Search Regex Url', 'search', ['required' => true, 'value' => $importData['search'] ?? null]) }}

                {{ Field::checkbox('Save Data', 'save', ['checked' => $importData['save'] ?? false]) }}

                <button type="submit" class="btn btn-success" data-loading-text="Loading...">Import</button>
            </div>

            <div class="col-md-8">
                <div id="inserts"></div>
                <ul id="results" class="list-group"></ul>
            </div>
        </div>
    </form>

    <style>
        .content-result {
            max-height: 600px;
            overflow-y: scroll;
        }

        .content-result img {
            max-width: 100%;
        }
    </style>

    <script type="text/javascript">
        function show_results(form, response) {
            $('#results').empty();
            if (response.data?.links) {
                $.each(response.data.links, function (index, row) {
                    $('#results').append(`<li>${row}</li>`);
                });
            }

            $('#inserts').html("Inserted "+ response.data?.inserts);

            $('.jw-message').remove();
        }
    </script>
@endsection
