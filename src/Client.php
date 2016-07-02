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
     * The default fields for messages.
     *
     * @var array
     */
    protected $messageDefaults;

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
     * @param  array  $messageDefaults
     * @param  \GuzzleHttp\Client  $httpClient
     */
    public function __construct($webhook, $messageDefaults = [], $httpClient = null)
    {
        $this->webhook = $webhook;

        $this->configureMessageDefaults($messageDefaults);

        $this->httpClient = $httpClient;
    }

    public function __call($name, $args)
    {
        $message = $this->createMessage();

        call_user_func_array([$message, $name], $args);

        return $message;
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
     * Get the http client.
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        if (!($this->httpClient instanceof HttpClient)) {
            $this->httpClient = new HttpClient([
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        }

        return $this->httpClient;
    }

    protected function configureMessageDefaults(array $messageDefaults)
    {
        $defaults = [
            MessageDefaults::MARKDOWN => true,
        ];

        $this->messageDefaults = $messageDefaults + $defaults;
    }

    public function getMessageDefaults($option = null)
    {
        return is_null($option) ?
            $this->messageDefaults :
            (isset($this->messageDefaults[$option]) ?  $this->messageDefaults[$option] : null);
    }

    public function createMessage()
    {
        return new Message($this);
    }

    /**
     * Send message, just alias to `sendPayload`.
     *
     * @param  mixed $message  A JSON string, or any arrayable object.
     * @return boolean
     */
    public function sendMessage($message)
    {
        return $this->sendPayload($message);
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

        $response = $this->getHttpClient()->post(
            $this->getWebhook(),
            ['body' => $payload]
        );

        return (200 === $response->getStatusCode());
    }
}
