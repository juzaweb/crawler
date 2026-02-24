@extends('core::layouts.admin')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <a href="{{ admin_url('crawler-sources') }}" class="btn btn-warning">
                <i class="fas fa-arrow-left"></i> {{ __('Back') }}
            </a>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6 offset-md-3">
            <form action="{{ route('admin.crawler-sources.import') }}" method="post" enctype="multipart/form-data">
                @csrf
                <x-card :title="$title ?? __('Import Crawler Sources')">
                    <div class="form-group mb-3">
                        <label for="file" class="form-label">{{ __('XML File') }}</label>
                        <input type="file" name="file" id="file" class="form-control" accept=".xml" required>
                    </div>

                    <div class="form-group text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> {{ __('Import') }}
                        </button>
                    </div>
                </x-card>
            </form>
        </div>
    </div>
@endsection
