@foreach($items as $item)
    @include('crawler::crawler-source.components.component-item', ['marker' => $item->name, 'item' => $item])
@endforeach
