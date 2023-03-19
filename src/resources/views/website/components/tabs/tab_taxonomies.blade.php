@foreach($taxonomies as $taxonomy)
    {{ Field::checkbox(
        $taxonomy->name,
        "pages[{$marker}][category_ids][{$taxonomy->taxonomy}][]",
        ['checked' => in_array($taxonomy->id, $model->category_ids[$taxonomy->taxonomy] ?? [])]
    ) }}
@endforeach
