<?php

namespace Juzaweb\Crawler\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Juzaweb\CMS\Contracts\HookActionContract;

class ImportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $types = app(HookActionContract::class)->getPostTypes()->keys();

        return [
            'template' => ['required'],
            'url' => ['required', 'url'],
            'type' => ['required', Rule::in($types)],
        ];
    }
}
