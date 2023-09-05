<div class="modal fade" id="{{ $name }}-modal" tabindex="-1" role="dialog" aria-labelledby="add-page-modal-label"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-page-modal-label">
                    {{ $title }}
                </h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <input type="hidden" name="pages[{{ $marker }}][id]" value="{{ $model->id ?? '' }}">

                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a href="#p{{ $marker }}_info-tab" class="nav-link active" id="p{{ $marker }}_info-label" data-toggle="tab" role="tab" data-turbolinks="false">Page Info</a>
                    </li>
                    <li class="nav-item">
                        <a href="#p{{ $marker }}_taxonomies-tab" class="nav-link" id="p{{ $marker }}_taxonomies-label" data-toggle="tab" role="tab" data-turbolinks="false">Taxonomies</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane p-3 active" id="p{{ $marker }}_info-tab" role="tabpanel" aria-labelledby="p{{ $marker }}_info-label">
                        @include('crawler::website.components.tabs.tab_info')
                    </div>
                    <div class="tab-pane p-3" id="p{{ $marker }}_taxonomies-tab" role="tabpanel" aria-labelledby="p{{ $marker }}_taxonomies-label">
                        @include('crawler::website.components.tabs.tab_taxonomies')
                    </div>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">
                    Save
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
