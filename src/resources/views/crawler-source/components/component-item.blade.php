<div class="row">
    <div class="col-md-4">
        {{ Field::text(__('Name'), "components[{$marker}][name]", ['value' => $item->name ?? '']) }}
    </div>

    <div class="col-md-4">
        {{ Field::text(__('Element'), "components[{$marker}][element]", ['value' => $item->element ?? '']) }}
    </div>

    <div class="col-md-4">
        {{ Field::select(__('Format'), "components[{$marker}][format]", ['value' => $item->format ?? []])->dropDownList(['text' => 'Text', 'html' => 'HTML']) }}
    </div>
</div>
