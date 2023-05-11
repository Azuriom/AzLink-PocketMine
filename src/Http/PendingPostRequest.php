<?php

namespace Azuriom\AzLink\PocketMine\Http;

use Azuriom\AzLink\PocketMine\Threaded\ArrayThreaded;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;
use Threaded;

class PendingPostRequest extends Threaded
{
    protected string $url;

    /**
     * @var ArrayThreaded
     */
    protected ArrayThreaded $data;
    /**
     * @var ArrayThreaded
     */
    protected ArrayThreaded $headers;

    public function __construct(string $url, array $data, array $headers)
    {
        $this->url = $url;
        $this->data = new ArrayThreaded($data);
        $this->headers = new ArrayThreaded($headers);
    }

    /**
     * Send the request.
     *
     * @return array
     *
     * @throws ClientExceptionInterface
     */
    public function send(): array
    {
        $request = new Request('POST', $this->url, $this->headers->toArray(), $this->encodeJson($this->data->toArray()));

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
