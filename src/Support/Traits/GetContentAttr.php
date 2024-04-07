<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Support\Traits;

trait GetContentAttr
{
    public function getTitle(): string
    {
        return $this->components['title'];
    }

    public function getContent(): string
    {
        return $this->components['content'];
    }

    public function getThumbnail(): ?string
    {
        return $this->components['thumbnail'] ?? null;
    }
}
