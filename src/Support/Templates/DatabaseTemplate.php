<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/cms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    MIT
 */

namespace Juzaweb\Crawler\Support\Templates;

use Juzaweb\Crawler\Interfaces\CrawlerTemplateInterface;
use Juzaweb\Crawler\Models\CrawlerTemplate as CrawlerTemplateModel;

class DatabaseTemplate extends CrawlerTemplate implements CrawlerTemplateInterface
{
    protected CrawlerTemplateModel $template;

    public function __construct(CrawlerTemplateModel $template)
    {
        $this->template = $template;
    }

    public function getLinkElement(): string
    {
        return $this->template->link_element;
    }

    public function getDataElements(): array
    {
        return (array) $this->template->data_elements;
    }
}
