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
			'domain' => ['required'],
			'active' => ['required', 'boolean'],
			'data_type' => ['required', 'in:posts'],
			'link_element' => ['nullable'],
			'link_regex' => ['nullable'],
			'components' => ['required', 'array'],
			'removes' => ['nullable', 'array'],
		];
    }
}
