<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('URL') }}</label>
            <input type="text" name="crawler_pages[{{ $marker }}][url]" class="form-control" value="{{ $item->url ?? '' }}" required>
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('URL With Page') }}</label>
            <input type="text"
                   name="crawler_pages[{{ $marker }}][url_with_page]"
                   class="form-control" value="{{ $item->url_with_page ?? '' }}"
            >
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            <label>{{ __('Category') }}</label>
            <select name="crawler_pages[{{ $marker }}][categories]" class="form-control load-categories">

            </select>
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            <label>{{ __('Locale') }}</label>
            <select name="crawler_pages[{{ $marker }}][locale]" class="form-control select2">
                @foreach($locales as $code => $locale)
                    <option value="{{ $code }}" {{ ($item->locale ?? app()->getLocale()) == $code ? 'selected' : '' }}>
                        {{ $locale['name'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-1 text-center">
         <div class="form-group">
            <label>{{ __('Active') }}</label>
            <input type="hidden" name="crawler_pages[{{ $marker }}][active]" value="0">
            <input type="checkbox" name="crawler_pages[{{ $marker }}][active]" value="1" {{ ($item->active ?? true) ? 'checked' : '' }}>
        </div>
    </div>

    <input type="hidden" name="crawler_pages[{{ $marker }}][id]" value="{{ $item->id ?? '' }}">
</div>
