@extends('core::layouts.admin')

@section('content')
    <form action="{{ $action }}" class="form-ajax" method="post">
        @if($model->exists)
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
                    {{ Field::text(__('Name'), 'name', ['value' => $model->name]) }}

					<div class="row">
                        <div class="col-md-6">
                            {{ Field::text(__('Link Element'), 'link_element', ['value' => $model->link_element]) }}
                        </div>

                        <div class="col-md-6">
                            {{ Field::text(__('Link Regex'), 'link_regex', ['value' => $model->link_regex]) }}
                        </div>
                    </div>
                </x-card>

                <x-card title="{{ __('Components') }}">
                    <x-repeater
                            name="components"
                            :items="collect($model->components ?? [])->map(fn($item) => (object) $item)->toArray()"
                            view="crawler::crawler-source.components.component-item"
                        />
                </x-card>
            </div>

            <div class="col-md-3">
                {{ Field::checkbox(__('Active'), 'active', ['value' => $model->active]) }}

                {{ Field::text(__('Data Type'), 'data_type', ['value' => $model->data_type]) }}
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script type="text/javascript" nonce="{{ csp_script_nonce() }}">
        $(function () {
            //
        });
    </script>
@endsection
