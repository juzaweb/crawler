<div class="row">
    <div class="col-md-4">
        {{ Field::text(__('Name'), "components[{$marker}][name]", ['value' => $item->name ?? '']) }}
    </div>

    <div class="col-md-4">
        {{ Field::text(__('Element'), "components[{$marker}][element]", ['value' => $item->element ?? '']) }}
    </div>

    <div class="col-md-2">
        {{ Field::text(__('Attribute'), "components[{$marker}][attr]", ['value' => $item->attr ?? '']) }}
    </div>

    <div class="col-md-2">
        {{ Field::select(__('Format'), "components[{$marker}][format]", ['value' => $item->format ?? []])->dropDownList(['text' => 'Text', 'html' => 'HTML', 'array_text' => 'Array']) }}
    </div>
</div>
