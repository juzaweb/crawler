<div class="modal fade" id="crawler-import-modal" tabindex="-1" role="dialog" aria-labelledby="crawler-import-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ admin_url('ajax/crawler-import') }}" method="post" class="form-ajax" data-notify="1" data-success="crawlerImportSuccess">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="crawler-import-modal-label">{{ __('Import Content') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{ Field::select(trans('crawler::content.template'), 'template', ['options' => $templateOptions]) }}

                    {{ Field::text(trans('crawler::content.url'), 'url', ['placeholder' => __('URL to detail content')]) }}

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

<div class="modal fade" id="crawler-import-page-modal" tabindex="-1" role="dialog" aria-labelledby="crawler-import-page-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ admin_url('ajax/crawler-import-page') }}"
              method="post"
              class="form-ajax"
              id="crawler-form-import-page"
              data-notify="1"
              data-success="crawlerImportPageSuccess"
        >
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="crawler-import-modal-page-label">{{ __('Import') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    {{ Field::select(trans('crawler::content.template'), 'template', ['options' => $templateOptions]) }}

                    {{ Field::text(trans('crawler::content.url'), 'url', ['placeholder' => __('URL to page list contents')]) }}

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
    function submitImportFromUrl(urls, index = 0) {
        let formData = new FormData($('#crawler-form-import-page')[0]);
        formData.set('url', urls[index]);

        $.ajax({
            type: "POST",
            url: "{{ admin_url('ajax/crawler-import') }}",
            dataType: 'json',
            data: formData,
            cache: false,
            contentType: false,
            processData: false
        }).done(function (res) {
            show_notify(res);

            if (urls[index + 1]) {
                submitImportFromUrl(urls, index + 1);
            } else {
                $('#crawler-form-import-page').find('input[name=url]').val('');
                table.refresh();
            }
        });
    }

    function crawlerImportSuccess(form, res) {
        if (res.status) {
            form.find('input[name=url]').val('');
            table.refresh();
        }
    }

    function crawlerImportPageSuccess(form, res) {
        if (res.status) {
            if (res.data.urls.length > 0) {
                submitImportFromUrl(res.data.urls);
            } else {
                $('#crawler-form-import-page').find('input[name=url]').val('');
                table.refresh();
            }
        }
    }
</script>