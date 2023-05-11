<?php

namespace Azuriom\AzLink\PocketMine\Http;

use Azuriom\AzLink\PocketMine\Utils\Convertor;
use ThreadedArray;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;
use ThreadedBase;

class PendingPostRequest extends ThreadedBase
{
    protected string $url;

    /**
     * @var ThreadedArray
     */
    protected ThreadedArray $data;
    /**
     * @var ThreadedArray
     */
    protected ThreadedArray $headers;

    public function __construct(string $url, array $data, array $headers)
    {
        $this->url = $url;
        $this->data =  ThreadedArray::fromArray($data);
        $this->headers = ThreadedArray::fromArray($headers);
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
        $request = new Request('POST', $this->url, Convertor::threadArrayToArray($this->headers), $this->encodeJson(Convertor::threadArrayToArray($this->data)));

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
