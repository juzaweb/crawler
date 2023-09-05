<h1>{{ $results['title'] }}</h1>

<div class="mt-3 w-100 content-result">
    {!! $results['content'] !!}
</div>

@if(isset($results['thumbnail']))
    <img src="{{ $results['thumbnail'] }}" class="w-100" />
@endif

@foreach($results as $key => $result)
    @if(in_array($key, ['title', 'content', 'thumbnail'])) @continue @endif

    <div class="{{ $key }}-result">
        <h5>{{ $key }}</h5>
        @if(is_string($result))
            {{ $result }}
        @endif

        @if(is_array($result))
            @dump($result)
        @endif
    </div>
@endforeach


