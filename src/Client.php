<?php

namespace ElfSundae\BearyChat;

use GuzzleHttp\Client as HttpClient;

class Client
{
    /**
     * The BearyChat incoming webhook.
     *
     * @var string
     */
    protected $webhook;

    /**
     * The Guzzle http client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * Create a new Client.
     *
     * @param  string  $webhook
     * @param  \GuzzleHttp\Client  $httpClient
     */
    public function __construct($webhook, $httpClient = null)
    {
        $this->webhook = $webhook;

        $this->httpClient = $httpClient ?: new HttpClient([
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    /**
     * Get the webhook.
     *
     * @return string
     */
    public function getWebhook()
    {
        return $this->webhook;
    }

    /**
     * Set the webhook.
     *
     * @param  string  $webhook
     */
    public function setWebhook($webhook)
    {
        $this->webhook = $webhook;
    }

    /**
     * Send message payload.
     *
     * @param  mixed $payload  A JSON string, or any arrayable object.
     * @return boolean
     */
    public function sendPayload($payload)
    {
        if (!is_string($payload)) {
            $payload = json_encode((array)$payload);
        }

        $response = $this->httpClient->post(
            $this->getWebhook(),
            ['body' => $payload]
        );

        return (200 === $response->getStatusCode());
    }
}
