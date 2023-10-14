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
use Illuminate\Support\Facades\File;

class MakeTemplateCommand extends Command
{
    protected $signature = 'crawler:make-template';

    protected $description = 'Command description';

    public function handle(): int
    {
        $webs = json_decode(File::get(storage_path('webs.json')), true, 512, JSON_THROW_ON_ERROR);
        $data['name'] = $this->ask('Web');

        if (collect($webs)->where('name', $data['name'])->count()) {
            $this->error('Web already exists');
            return static::FAILURE;
        }

        $data['list'] = $this->ask('list');
        $data['data']['title'] = $this->ask('title');
        $data['data']['content'] = $this->ask('content');
        $data['data']['tags'] = $this->ask('tags');

        $webs[] = $data;
        File::put(
            storage_path('webs.json'),
            json_encode($webs, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return Command::SUCCESS;
    }
}
