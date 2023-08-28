<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/cms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    MIT
 */

namespace Juzaweb\Crawler\Support\Traists;

trait UseTemplateHasResource
{
    public function getResourceUrlWithPage(): ?string
    {
        return $this->resourceUrlWithPage ?? null;
    }
}
