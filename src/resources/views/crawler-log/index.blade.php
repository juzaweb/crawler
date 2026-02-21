@extends('core::layouts.admin')

@section('content')
    <div class="row">
        <div class="col-md-12 mt-2">
            <x-card title="{{ __('Crawler Logs') }}">
                @include('crawler::crawler-log.components.filters')

                {{ $dataTable->table() }}
            </x-card>
        </div>
    </div>
@endsection

@section('scripts')
    {{ $dataTable->scripts(null, ['nonce' => csp_script_nonce()]) }}

    <script nonce="{{ csp_script_nonce() }}">
        (function ($) {
            $('#apply-filter').on('click', function (e) {
                e.preventDefault();
                window.LaravelDataTables["jw-datatable"].draw();
            });
        })(jQuery);
    </script>
@endsection
