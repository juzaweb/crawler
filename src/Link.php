<?php
/**
 * LARABIZ CMS - Full SPA Laravel CMS
 *
 * @package    larabizcms/larabiz
 * @author     The Anh Dang
 * @link       https://larabiz.com
 */

namespace Juzaweb\Modules\Crawler;

class Link
{
    public array $result = [];

    public function __construct(public string $url, public array $elements = [])
    {
    }

    public function getValueFrom(string $html): array
    {
        foreach ($this->elements as $element => $handler) {
            $this->result[$element] = $handler->getValueFrom($html);
        }

        return $this->result;
    }
}
