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

class CrawlerSourceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
			'name' => ['required', 'string', 'max:100'],
			'active' => ['required', 'boolean'],
			'data_type' => ['required', 'in:posts'],
			'link_element' => ['nullable', 'string', 'max:200'],
			'link_regex' => ['nullable', 'string', 'max:200'],
			'components' => ['required', 'array'],
			'removes' => ['nullable', 'array'],
            'crawler_pages' => ['nullable', 'array'],
            'crawler_pages.*.id' => ['nullable', 'uuid'],
            'crawler_pages.*.url' => ['required', 'string', 'max:190'],
            'crawler_pages.*.active' => ['nullable', 'boolean'],
		];
    }
}
