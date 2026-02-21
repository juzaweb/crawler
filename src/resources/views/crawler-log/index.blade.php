@extends('core::layouts.admin')

@section('content')
    <div class="row">
        <div class="col-md-12">
            @component('core::components.datatables.filters')
                <div class="col-md-3 jw-datatable_filters">
                    <select name="source_id" class="form-control select2-default">
                        <option value="">{{ __('Source') }}</option>
                        @foreach($sources as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 jw-datatable_filters">
                    <select name="page_id" class="form-control select2-default">
                        <option value="">{{ __('Page') }}</option>
                        @foreach($pages as $id => $url)
                            <option value="{{ $id }}">{{ $url }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 jw-datatable_filters">
                    <select name="status" class="form-control select2-default">
                        <option value="">{{ __('Status') }}</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}">{{ strtoupper($status->value) }}</option>
                        @endforeach
                    </select>
                </div>
            @endcomponent
        </div>

        <div class="col-md-12">
            <x-card title="{{ __('Crawler Logs') }}">
                {{ $dataTable->table() }}
            </x-card>
        </div>
    </div>
@endsection

@section('scripts')
    {{ $dataTable->scripts(null, ['nonce' => csp_script_nonce()]) }}
@endsection
