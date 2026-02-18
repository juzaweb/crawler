@extends('admin::layouts.admin')

@section('content')
    <div class="row">
        <div class="col-md-12">
            @can('Crawler Sources.create')
                <a href="{{ $createUrl }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ __('Add Crawler Source') }}
                </a>
            @endcan
        </div>
    </div>

    <div class="row mt-3">
        {{--<div class="col-md-12">
            <x-admin::datatables.filters>
                <div class="col-md-3 jw-datatable_filters">

                </div>
            </x-admin::datatables.filters>
        </div>--}}

        <div class="col-md-12 mt-2">
            <x-card title="{{ __('Crawler Sources') }}">
                {{ $dataTable->table() }}
            </x-card>
        </div>
    </div>
@endsection

@section('scripts')
    {{ $dataTable->scripts(null, ['nonce' => csp_script_nonce()]) }}
@endsection
