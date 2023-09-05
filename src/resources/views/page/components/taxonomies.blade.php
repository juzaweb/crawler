@foreach($taxonomies as $taxonomy)
    <div class="{{ ($level ?? 0) > 0 ? "ml-{$level} pl-{$level}" : '' }}">
        {{ Field::checkbox(
            $taxonomy->name,
            "category_ids[{$taxonomy->taxonomy}][]",
            [
                'checked' => in_array($taxonomy->id, $model->category_ids[$taxonomy->taxonomy] ?? []),
                'value' => $taxonomy->id,
            ]
        ) }}
    </div>

    @if($taxonomy->recursiveChildren)
        @component('crawler::page.components.taxonomies', [
            'taxonomies' => $taxonomy->recursiveChildren,
            'model' => $model,
            'level' => ($level ?? 0) + 1,
        ])

        @endcomponent
    @endif

@endforeach
