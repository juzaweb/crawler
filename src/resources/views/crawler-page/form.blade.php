@extends('core::layouts.admin')

@section('content')
    <form action="{{ $action }}" class="form-ajax" method="post">
        @if ($model->exists)
            @method('PUT')
        @endif


        <div class="row">
            <div class="col-md-12">
                <a href="{{ $backUrl }}" class="btn btn-warning">
                    <i class="fas fa-arrow-left"></i> {{ __('Back') }}
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ __('Save') }}
                </button>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-9">
                <x-card title="{{ __('Information') }}">
                    {{ Field::text(__('Url'), 'url', ['value' => $model->url]) }}

                    {{ Field::text(__('Url With Page'), 'url_with_page', ['value' => $model->url_with_page]) }}

                    {{ Field::text(__('Next Page'), 'next_page', ['value' => $model->next_page]) }}

                    {{ Field::select(__('Locale'), 'locale', ['options' => collect($locales)->map(fn($item) => $item['name'])->toArray(), 'value' => $model->locale ?? app()->getLocale()]) }}
                </x-card>
            </div>

            <div class="col-md-3">

                {{ Field::checkbox(__('Active'), 'active', ['value' => $model->active]) }}
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script type="text/javascript" nonce="{{ csp_script_nonce() }}">
        $(function() {
            //
        });
    </script>
@endsection
