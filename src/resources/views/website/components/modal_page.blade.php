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

                @component('cms::components.tabs', [
                    'tabs' => [
                        'info' => ['label' => 'Page Info'],
                        'taxonomies' => ['label' => 'Taxonomies']
                    ]
                ])

                    @slot('tab_info')
                        @include('crawler::website.components.tabs.tab_info')
                    @endslot

                @endcomponent

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
