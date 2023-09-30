<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/juzacms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Crawler\Jobs\Bus;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Juzaweb\Crawler\Contracts\CrawlerContract;
use Juzaweb\Crawler\Models\CrawlerContent;
use Juzaweb\Crawler\Models\CrawlerLink;
use Juzaweb\Proxies\Contracts\ProxyManager;
use Juzaweb\Proxies\Models\Proxy;
use Throwable;

class TranslateContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 7200;

    public bool $failOnTimeout = true;

    protected bool $isServerTranslate = false;

    protected Proxy $proxy;

    protected int $reQueueDelay = 600;

    public function __construct(protected CrawlerLink $link, protected string $target)
    {
    }

    public function handle(): void
    {
        if (!$this->isServerTranslateLock() && (bool) get_config('crawler_without_proxy', 1)) {
            $this->lockServerTranslate();

            $this->isServerTranslate = true;

            $this->translate();

            $this->unlockServerTranslate();
            return;
        }

        if ((bool) get_config('crawler_enable_proxy', 0)) {
            $proxy = app(ProxyManager::class)->free();

            if ($proxy === null) {
                $this->release($this->reQueueDelay);
                return;
            }

            $this->proxy = $proxy;
            $this->translate($proxy);

            $proxy->update(['is_free' => true]);
            return;
        }

        $this->release($this->reQueueDelay);
    }

    public function failed(Throwable $exception): void
    {
        if ($this->isServerTranslate) {
            $this->unlockServerTranslate();
        }

        if (isset($this->proxy)) {
            $this->proxy->update(['is_free' => true]);
        }
    }

    protected function translate(?Proxy $proxy = null): bool
    {
        $crawler = app(CrawlerContract::class);
        $content = $this->link->contents()->where(['is_source' => 1])->first();

        try {
            $crawler->translate($content, $this->target, $proxy?->toGuzzleHttpProxy());

            $content->update(['status' => CrawlerContent::STATUS_DONE]);
        } catch (Throwable $e) {
            $content->update(['status' => CrawlerContent::STATUS_ERROR]);
            report($e);
            return false;
        }

        return true;
    }

    protected function unlockServerTranslate(): bool
    {
        return Storage::disk('local')->delete('crawler-server-translate.json');
    }

    protected function lockServerTranslate(): bool
    {
        return Storage::disk('local')->put(
            'crawler-server-translate.json',
            json_encode(['time' => time()], JSON_THROW_ON_ERROR)
        );
    }

    protected function isServerTranslateLock(): bool
    {
        if (!Storage::disk('local')->exists('crawler-server-translate.json')) {
            return false;
        }

        $data = json_decode(
            Storage::disk('local')->get('crawler-server-translate.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if ($data['time'] + 7200 < time()) {
            $this->unlockServerTranslate();
            return false;
        }

        return true;
    }
}
