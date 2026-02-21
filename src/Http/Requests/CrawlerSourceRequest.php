<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 */

namespace Juzaweb\Modules\Crawler\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Juzaweb\Modules\Crawler\Facades\Crawler;

class CrawlerSourceRequest extends FormRequest
{
    public function rules(): array
    {
        $types = array_keys(Crawler::getDataTypes());

        return [
			'name' => ['required', 'string', 'max:100'],
			'active' => ['required', 'boolean'],
			'data_type' => ['required', Rule::in($types)],
			'link_element' => ['nullable', 'string', 'max:200'],
			'link_regex' => ['nullable', 'string', 'max:200'],
			'components' => ['required', 'array'],
			'removes' => ['nullable', 'array'],
            'crawler_pages' => ['nullable', 'array'],
            'crawler_pages.*.id' => ['nullable', 'uuid'],
            'crawler_pages.*.url' => ['required', 'string', 'max:190'],
            'crawler_pages.*.url_with_page' => ['nullable', 'string', 'max:190'],
            'crawler_pages.*.active' => ['nullable', 'boolean'],
		];
    }
}
