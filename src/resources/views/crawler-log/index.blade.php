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

    <div class="modal fade" id="modal-show-error" tabindex="-1" aria-labelledby="modal-show-error-label" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-show-error-label">{{ __('Error Details') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <pre><code id="modal-show-error-content"></code></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
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

            $(document).on('click', '.show-log-error', function () {
                var error = $(this).data('error');

                if (typeof error === 'object') {
                    error = JSON.stringify(error, null, 2);
                }

                $('#modal-show-error-content').text(error);
                $('#modal-show-error').modal('show');
            });
        })(jQuery);
    </script>
@endsection
