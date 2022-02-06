<?php

namespace Azuriom\AzLink\PocketMine\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use JsonException;
use RuntimeException;

class PendingPostRequest
{
    protected string $url;

    protected array $data;

    protected array $headers;

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
            require __DIR__.'/../../vendor/autoload.php';
        }

        $request = new Request('POST', $this->url, $this->headers, $this->encodeJson($this->data));

        $response = (new Client())->send($request);

        try {
            return json_decode($response->getBody(), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException("Invalid JSON received ({$e->getMessage()}): {$response->getBody()}");
        }
    }

    protected function encodeJson(array $value): string
    {
        try {
            return json_encode($value, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('JSON encode error: '.$e->getMessage());
        }
    }
}
