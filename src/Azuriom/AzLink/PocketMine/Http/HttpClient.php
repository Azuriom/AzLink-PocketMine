<?php

namespace Azuriom\AzLink\PocketMine\Http;

use Azuriom\AzLink\PocketMine\AzLinkPM;
use GuzzleHttp\Client;

class HttpClient
{
    /** @var AzLinkPM */
    protected $plugin;

    /** @var Client */
    protected $client;

    public function __construct(AzLinkPM $plugin)
    {
        $this->plugin = $plugin;
        $this->client = new Client();
    }

    /**
     * Verify the website connection.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getStatus()
    {
        return $this->client->get($this->getUrl(), [
            'headers' => $this->getHeaders(),
        ]);
    }

    /**
     * Send the server data to the website.
     *
     * @param  array  $data
     * @return \Azuriom\AzLink\PocketMine\Http\PendingPostRequest
     */
    public function preparePostRequest(array $data): PendingPostRequest
    {
        return new PendingPostRequest($this->getUrl(), $data, $this->getHeaders());
    }

    protected function getUrl(): string
    {
        return $this->plugin->getConfig()->get('siteUrl').'/api/azlink';
    }

    protected function getHeaders(): array
    {
        $version = $this->plugin->getDescription()->getDescription();
        $key = $this->plugin->getConfig()->get('siteKey');

        return [
            'User-Agent' => 'AzLink PocketMine v'.$version,
            'Azuriom-Link-Token' => $key,
        ];
    }
}
