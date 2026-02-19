<div class="row">
    <div class="col-md-10">
        <div class="form-group">
            <label>{{ __('URL') }}</label>
            <input type="text" name="crawler_pages[{{ $marker }}][url]" class="form-control" value="{{ $item->url ?? '' }}" required>
        </div>
    </div>
    <div class="col-md-2">
         <div class="form-group">
            <label>{{ __('Active') }}</label>
            <input type="hidden" name="crawler_pages[{{ $marker }}][active]" value="0">
            <input type="checkbox" name="crawler_pages[{{ $marker }}][active]" value="1" {{ ($item->active ?? true) ? 'checked' : '' }}>
        </div>
    </div>
    @if($item->id ?? false)
        <input type="hidden" name="crawler_pages[{{ $marker }}][id]" value="{{ $item->id }}">
    @endif
</div>
