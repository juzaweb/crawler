@extends('core::layouts.admin')

@section('content')
    <div class="row">
        <div class="col-md-12 mt-2">
            <x-card title="{{ __('Crawler Logs') }}">
                {{ $dataTable->table() }}
            </x-card>
        </div>
    </div>
@endsection

@section('scripts')
    {{ $dataTable->scripts(null, ['nonce' => csp_script_nonce()]) }}
@endsection
