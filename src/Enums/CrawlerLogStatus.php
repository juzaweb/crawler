<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Crawler\Enums;

enum CrawlerLogStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case RETRYING = 'retrying';
    case CRAWLED = 'crawled';
    case POSTING = 'posting';
    case FAILED_POSTING = 'failed_posting';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            self::RETRYING => 'Retrying',
            self::CRAWLED => 'Crawled',
            self::POSTING => 'Posting',
            self::FAILED_POSTING => 'Failed Posting',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'secondary',
            self::PROCESSING => 'info',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
            self::RETRYING => 'warning',
            self::CRAWLED => 'primary',
            self::POSTING => 'info',
            self::FAILED_POSTING => 'danger',
        };
    }
}
