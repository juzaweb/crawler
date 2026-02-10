<?php

namespace Juzaweb\Modules\Crawler\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Juzaweb\Modules\Core\Rules\AllExist;

class CrawlerSourceActionsRequest extends FormRequest
{
    public function rules()
    {
        return [
            'action' => ['required'],
            'ids' => ['required', 'array', 'min:1', new AllExist('crawler_sources', 'id')],
        ];
    }
}
