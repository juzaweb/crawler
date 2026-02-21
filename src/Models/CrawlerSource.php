<?php

namespace Juzaweb\Modules\Crawler\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Juzaweb\Modules\Core\Models\Model;
use Juzaweb\Modules\Core\Traits\HasAPI;
use Juzaweb\Modules\Crawler\Contracts\CrawlerDataType;
use Juzaweb\Modules\Crawler\Elements\ArrayStringElement;
use Juzaweb\Modules\Crawler\Elements\HtmlElement;
use Juzaweb\Modules\Crawler\Elements\StringElement;
use Juzaweb\Modules\Crawler\Facades\Crawler;
use function PHPUnit\Framework\matches;

class CrawlerSource extends Model
{
    use HasAPI, HasUuids;

    protected $table = 'crawler_sources';

    protected $fillable = [
        'name',
        'active',
        'data_type',
        'link_element',
        'link_regex',
        'components',
        'removes',
    ];

    protected $casts = [
        'active' => 'boolean',
        'components' => 'array',
        'removes' => 'array',
    ];

    public function pages(): HasMany
    {
        return $this->hasMany(CrawlerPage::class, 'source_id', 'id');
    }

    public function getDataType(): ?CrawlerDataType
    {
        return Crawler::getDataType($this->data_type);
    }

    public function getLinkElement(): string
    {
        return $this->link_element;
    }

    public function getLinkRegex(): ?string
    {
        return $this->link_regex;
    }

    public function getComponents(): array
    {
        return $this->components ?? [];
    }

    public function getRemoves(): array
    {
        return $this->removes ?? [];
    }

    public function mapComponentsWithElements(string $url): array
    {
        $result = [];

        foreach ($this->components as $key => $component) {
            if (! isset($component['element'])) {
                continue;
            }

            $result[$key] = match ($component['format']) {
                'text' => StringElement::make($component['element'], $component['attr'] ?? null),
                'html' => HtmlElement::make($component['element'], $component['attr'] ?? null)
                    ->removeInternalLinks($url)
                    ->removeElements($this->getRemoves()),
                'array_text' => ArrayStringElement::make($component['element'], $component['attr'] ?? null),
                default => null,
            };
        }

        return $result;
    }
}
