<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Crawler\Facades;

use Illuminate\Support\Facades\Facade;

class Crawler extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Juzaweb\Modules\Crawler\Contracts\Crawler::class;
    }
}
