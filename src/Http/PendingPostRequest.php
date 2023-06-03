<?php

namespace Azuriom\AzLink\PocketMine\Http;

use Azuriom\AzLink\PocketMine\Utils\Convertor;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use JsonException;
use pmmp\thread\ThreadSafe;
use pmmp\thread\ThreadSafeArray;
use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

class PendingPostRequest extends ThreadSafe
{
    protected string $url;

    /**
     * @var ThreadSafeArray
     */
    protected ThreadSafeArray $data;
    /**
     * @var ThreadSafeArray
     */
    protected ThreadSafeArray $headers;

    public function __construct(string $url, array $data, array $headers)
    {
        $this->url = $url;
        $this->data =  ThreadSafeArray::fromArray($data);
        $this->headers = ThreadSafeArray::fromArray($headers);
    }

    /**
     * Send the request.
     *
     * @return ThreadSafeArray
     *
     * @throws ClientExceptionInterface
     */
    public function send(): ThreadSafeArray
    {
        $request = new Request('POST', $this->url, Convertor::threadArrayToArray($this->headers), $this->encodeJson(Convertor::threadArrayToArray($this->data)));

        $response = (new Client())->send($request);

        try {
            return ThreadSafeArray::fromArray(json_decode($response->getBody(), true, flags: JSON_THROW_ON_ERROR));
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
