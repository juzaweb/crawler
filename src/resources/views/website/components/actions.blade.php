<div class="btn-group">
    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('admin.crawler.websites.pages.index', [$row->id]) }}">Pages</a>
        <a class="dropdown-item" href="{{ route('admin.crawler.websites.contents.index', [$row->id]) }}">Contents</a>
    </div>
</div>
