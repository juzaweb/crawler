<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

return [
    'queue' => [
        'crawler' => env('CRAWLER_QUEUE', 'default'),

        'translate' => env('CRAWLER_TRANSLATE_QUEUE', 'default'),
    ]
];
