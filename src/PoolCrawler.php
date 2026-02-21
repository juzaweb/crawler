<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Crawler;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;

class PoolCrawler
{
    protected array $result = [];

    protected int $concurrency = 5;

    public function __construct(protected Collection $pages)
    {
    }

    public function crawl(): static
    {
        $pages = $this->pages;
        $client = $this->http();

        $requests = function ($pages) {
            foreach ($pages as $page) {
                yield new Request('GET', $page->url);
            }
        };

        $pool = new Pool($client, $requests($pages), [
            'concurrency' => $this->concurrency,
            'fulfilled' => function (Response $response, $index) use ($pages) {
                $page = $pages[$index];
                $content = reformat_html($response->getBody()->getContents());

                try {
                    $this->result[$index] = $page->getValueFrom($content);
                } catch (\Throwable $e) {
                    report($e);
                    $this->result[$index] = ['error' => $e->getMessage()];
                }
            },
            'rejected' => function (RequestException|ConnectException $reason, $index) {
                $this->result[$index] = ['error' => $reason->getMessage()];
            },
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();

        return $this;
    }

    public function getResult(): Collection
    {
        return new Collection($this->result);
    }

    protected function http(): Client
    {
        return new Client([
            'timeout' => 10,
            'connect_timeout' => 10,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 '
                    . '(KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36',
            ],
        ]);
    }
}
