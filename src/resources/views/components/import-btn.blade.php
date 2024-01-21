<div class="dropdown">
    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        {{ trans('cms::app.import') }}
    </button>
    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#crawler-import-modal">
            {{ __('Import Content') }}
        </a>
        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#crawler-import-page-modal">
            {{ __('Import Content in Page') }}
        </a>
    </div>
</div>
