<?php
/**
 * LARABIZ CMS - Full SPA Laravel CMS
 *
 * @package    larabizcms/larabiz
 * @author     The Anh Dang
 * @link       https://larabiz.com
 */

namespace Juzaweb\Modules\Crawler;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;

class Crawler
{
    protected array $result = [];

    protected int $concurrency = 5;

    public static function make(): static
    {
        return new static();
    }

    /**
     * @param  Collection<int, Link>  $pages
     * @return static
     */
    public function crawl(Collection $pages): static
    {
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
