<?php

namespace Azuriom\AzLink\PocketMine\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use RuntimeException;

class PendingPostRequest
{
    /** @var string */
    protected $url;

    /** @var array */
    protected $data;

    /** @var array */
    protected $headers;

    public function __construct(string $url, array $data, array $headers)
    {
        $this->url = $url;
        $this->data = $data;
        $this->headers = $headers;
    }

    /**
     * Send the request.
     *
     * @return array
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function send(): array
    {
        if (! class_exists(Client::class)) {
            require __DIR__.'/../../../../../vendor/autoload.php';
        }

        $request = new Request('POST', $this->url, $this->headers, \GuzzleHttp\json_encode($this->data));

        $response = (new Client())->sendRequest($request);

        $data = json_decode($response->getBody(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON received ('.json_last_error_msg().'): '.$response->getBody());
        }

        return $data;
    }
}
