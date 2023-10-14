@extends('cms::layouts.backend')

@section('content')
    <form class="form-ajax" method="post" data-success="show_results">
        <div class="row">
            <div class="col-md-4">
                {{ Field::select('Template', 'template', ['options' => $templateOptions, 'value' => $testingData['template'] ?? null]) }}

                {{ Field::text('URL', 'url', ['required' => true, 'value' => $testingData['url'] ?? null]) }}

                {{ Field::select(
                    'Option',
                    'option',
                    [
                        'options' => ['content' => 'Get Content', 'link' => 'Get Link'],
                        'value' => $testingData['option'] ?? null,
                    ],
                ) }}

                <button type="submit" class="btn btn-success" data-loading-text="Loading...">Test</button>
            </div>

            <div class="col-md-8">
                <div id="results"></div>
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
            $('#results').html(response.data.html);
            $('.jw-message').remove();
        }
    </script>
@endsection
