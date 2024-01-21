<div class="modal fade" id="crawler-import-modal" tabindex="-1" role="dialog" aria-labelledby="crawler-import-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ admin_url('ajax/crawler-import') }}" method="post" class="form-ajax" data-notify="1" data-success="crawlerImportSuccess">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="crawler-import-modal-label">{{ __('Import') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{ Field::select(trans('crawler::content.template'), 'template', ['options' => $templateOptions]) }}

                    {{ Field::text(trans('crawler::content.url'), 'url') }}

                    @foreach($taxonomies as $taxonomy)
                        {{ Field::selectTaxonomy($taxonomy->get('label'), $taxonomy->get('taxonomy'), [
                            'post_type' => $setting->get('key'),
                            'taxonomy' => $taxonomy->get('taxonomy'),
                            'multiple' => true,
                        ]) }}
                    @endforeach

                    <input type="hidden" name="type" value="{{ $setting->get('key') }}">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">{{ trans('cms::app.import') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function crawlerImportSuccess(form, res) {
        if (res.status) {
            form.find('input[name=url]').val('');
            table.refresh();
        }
    }
</script>