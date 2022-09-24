<tr>
    <td class="page-url">
        {{ $model->url ?? '' }}
    </td>
    <td align="center">
        {!! ($model->auto_craw ?? 0) == 1 ? '<span class="text-success"><i class="fa fa-check"></i></span>' : '<span class="text-danger"><i class="fa fa-times"></i></span>' !!}
    </td>
    <td align="center">
        {!! ($model->active ?? 0) == 1 ? '<span class="text-success"><i class="fa fa-check"></i></span>' : '<span class="text-danger"><i class="fa fa-times"></i></span>' !!}
    </td>
    <td>
        <a href="javascript:void(0)"
           class="text-primary"
           data-toggle="modal"
           data-target="#page-{{ ($model->id ?? '') }}-modal"
        >
            <i class="fa fa-edit"></i>
        </a>

        <a href="javascript:void(0)" class="text-danger remove-page-item">
            <i class="fa fa-trash"></i>
        </a>

        @component('crawler::website.components.modal_page', [
            'name' => 'page-'. ($model->id ?? ''),
            'model' => $model ?? null,
            'marker' => $marker,
            'types' => $types,
            'title' => ($model->id ?? '') ? 'Page '.$model->id : 'New Page',
        ])

        @endcomponent
    </td>
</tr>
