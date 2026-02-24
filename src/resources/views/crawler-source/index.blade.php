@extends('core::layouts.admin')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="btn-group">
                @can('Crawler Sources.create')
                    <a href="{{ $createUrl }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('Add Crawler Source') }}
                    </a>

                    <a href="javascript:void(0)" class="btn btn-success" data-toggle="modal" data-target="#import-modal">
                        <i class="fas fa-upload"></i> {{ __('Import') }}
                    </a>
                @endcan

                @can('Crawler Sources.index')
                    <a href="{{ route('admin.crawler-sources.export') }}" class="btn btn-info">
                        <i class="fas fa-download"></i> {{ __('Export') }}
                    </a>
                @endcan
            </div>
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

    <div class="modal fade" id="import-modal" tabindex="-1" role="dialog" aria-labelledby="import-modal-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.crawler-sources.import') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="import-modal-label">{{ __('Import Crawler Sources') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="file" class="form-label">{{ __('XML File') }}</label>
                            <input type="file" name="file" id="file" class="form-control" accept=".xml" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Import') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{ $dataTable->scripts(null, ['nonce' => csp_script_nonce()]) }}
@endsection
