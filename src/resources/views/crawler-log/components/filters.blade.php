<div class="row mb-3">
    <div class="col-md-3">
        <select name="source_id" id="source_id" class="form-control select2-default">
            <option value="">{{ __('Source') }}</option>
            @foreach($sources as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="page_id" id="page_id" class="form-control select2-default">
            <option value="">{{ __('Page') }}</option>
            @foreach($pages as $id => $url)
                <option value="{{ $id }}">{{ $url }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="status" id="status" class="form-control select2-default">
            <option value="">{{ __('Status') }}</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}">{{ strtoupper($status->value) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <button type="button" class="btn btn-primary" id="apply-filter">
            {{ __('Filter') }}
        </button>
    </div>
</div>
