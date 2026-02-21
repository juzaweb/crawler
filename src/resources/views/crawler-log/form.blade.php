@extends('core::layouts.admin')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <a href="{{ admin_url('crawler-logs') }}" class="btn btn-warning">
                <i class="fas fa-arrow-left"></i> {{ __('Back') }}
            </a>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <x-card title="{{ __('Crawler Log Information') }}">
                <div class="row">
                    <div class="col-md-6">
                        {{ Field::text(__('URL'), 'url', ['value' => $model->url, 'disabled' => true]) }}
                    </div>

                    <div class="col-md-3">
                        {{ Field::text(__('Status'), 'status', ['value' => $model->status->value, 'disabled' => true]) }}
                    </div>

                    <div class="col-md-3">
                        {{ Field::text(__('Source'), 'source', ['value' => $model->source?->name, 'disabled' => true]) }}
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <label class="form-label">{{ __('Content JSON') }}</label>
                        <textarea class="form-control" rows="10" disabled>{{ json_encode($model->content_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                    </div>
                </div>

                @if($model->error)
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label class="form-label">{{ __('Error') }}</label>
                        <textarea class="form-control text-danger" rows="5" disabled>{{ json_encode($model->error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                    </div>
                </div>
                @endif
            </x-card>
        </div>
    </div>
@endsection
