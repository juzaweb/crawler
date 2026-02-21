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

class CrawlerPageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
			'url' => ['required'],
			'url_with_page' => ['required'],
			'next_page' => ['required'],
			'active' => ['required'],
			'locale' => ['required'],
			'categories' => ['nullable', 'array'],
		];
    }
}
