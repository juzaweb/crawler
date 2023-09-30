<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Juzaweb\Crawler\Models\CrawlerTemplate;
use Juzaweb\Crawler\Support\CrawlerElement;
use Symfony\Component\Console\Input\InputOption;

class ImportTemplateCommand extends Command
{
    protected $name = 'crawler:import-template';

    protected $description = 'Import template';

    public function handle(): void
    {
        $path = base_path($this->option('path'));

        if (!file_exists($path)) {
            $this->error('File not found');
            return;
        }

        $webs = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        foreach ($webs as $web) {
            $template = CrawlerTemplate::updateOrCreate(
                ['name' => $web['name']],
                [
                    'link_element' => $web['list'],
                    'data_elements' => [
                        'data' => $this->parseData(Arr::get($web, 'data', [])),
                        'removes' => Arr::get($web, 'removes', []),
                    ],
                ]
            );

            $this->info("Imported template: {$template->name}");
        }
    }

    protected function parseData(array $items): array
    {
        foreach ($items as $key => $item) {
            if (is_array($item)) {
                continue;
            }

            switch ($key) {
                case 'title':
                    $items[$key] = [
                        'selector' => $item,
                        'index' => 0,
                        'value' => CrawlerElement::$VALUE_TEXT,
                    ];
                    break;
                case 'content':
                    $items[$key] = [
                        'selector' => $item,
                        'index' => 0,
                    ];
                    break;
                case 'tags':
                    $items[$key] = [
                        'selector' => $item,
                        'value' => CrawlerElement::$VALUE_TEXT,
                    ];
                    break;
            }
        }

        if (empty($items['thumbnail'])) {
            $items['thumbnail'] = [
                'selector' => 'meta[property="og:image"]',
                'attr' => 'content',
                'index' => 0,
            ];
        }

        return $items;
    }

    protected function getOptions(): array
    {
        return [
            ['path', null, InputOption::VALUE_OPTIONAL, 'Path', 'storage/webs.json'],
        ];
    }
}
