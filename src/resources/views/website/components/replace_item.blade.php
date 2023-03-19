<tr>
    <td>
        <input class="form-control" name="translate_replaces[{{ $marker }}][search]" placeholder="Search" value="{{ $item['search'] ?? '' }}">
    </td>
    <td align="center">
        <input class="form-control" name="translate_replaces[{{ $marker }}][replace]" placeholder="Search" value="{{ $item['replace'] ?? '' }}">
    </td>
    <td class="text-center">
        <a href="javascript:void(0)" class="text-danger remove-replace-item">
            <i class="fa fa-trash"></i>
        </a>
    </td>
</tr>
