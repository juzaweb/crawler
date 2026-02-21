<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('Element') }}</label>
            <input type="text" name="removes[{{ $marker }}][element]" class="form-control" value="{{ $item['element'] ?? '' }}">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('Index') }}</label>
            <input type="text" name="removes[{{ $marker }}][index]" class="form-control" value="{{ $item['index'] ?? '' }}">
        </div>
    </div>
</div>
