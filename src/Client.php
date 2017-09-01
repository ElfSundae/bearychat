<?php

namespace ElfSundae\BearyChat;

use JsonSerializable;
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
    protected $messageDefaults = [];

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
     * @param  array  $messageDefaults  See `\ElfSundae\BearyChat\MessageDefaults`
     * @param  \GuzzleHttp\Client  $httpClient
     */
    public function __construct($webhook = null, $messageDefaults = [], $httpClient = null)
    {
        $this->webhook($webhook)
            ->messageDefaults($messageDefaults);
        $this->httpClient = $httpClient;
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
     * @return $this
     */
    public function setWebhook($webhook)
    {
        $this->webhook = $webhook;

        return $this;
    }

    /**
     * Change the webhook URL.
     *
     * @param  string  $webhook
     * @return $this
     */
    public function webhook($webhook)
    {
        return $this->setWebhook($webhook);
    }

    /**
     * Retrieve message defaults.
     *
     * @param  string|null  $key
     * @return mixed
     */
    public function getMessageDefaults($key = null)
    {
        if (is_null($key)) {
            return $this->messageDefaults;
        }

        if (isset($this->messageDefaults[$key])) {
            return $this->messageDefaults[$key];
        }
    }

    /**
     * Set the message defaults.
     *
     * @param  array  $defaults
     * @return $this
     */
    public function setMessageDefaults($defaults)
    {
        $this->messageDefaults = (array) $defaults;

        return $this;
    }

    /**
     * Set the message defaults.
     *
     * @param  array  $defaults
     * @return $this
     */
    public function messageDefaults($defaults)
    {
        return $this->setMessageDefaults($defaults);
    }

    /**
     * Create a new Message instance.
     *
     * @return \ElfSundae\BearyChat\Message
     */
    public function createMessage()
    {
        return new Message($this);
    }

    /**
     * Send message to the BearyChat.
     *
     * @param  mixed $message  A JSON string, or any arrayable object.
     * @return bool
     */
    public function sendMessage($message)
    {
        if ($payload = $this->getJsonPayload($message)) {
            $response = $this->getHttpClient()
                ->post($this->getWebhook(), [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => $payload,
                ]);

            return 200 === $response->getStatusCode();
        }

        return false;
    }

    /**
     * Get the JSON payload from an object.
     *
     * @param  mixed  $message
     * @return string
     */
    protected function getJsonPayload($message)
    {
        if (is_array($message) || $message instanceof JsonSerializable) {
            return json_encode($message);
        }

        if (method_exists($message, 'toJson')) {
            return $message->toJson();
        }

        if (method_exists($message, 'toArray')) {
            return json_encode($message->toArray());
        }

        if (is_string($message) && is_array(json_decode($message, true))) {
            return $message;
        }
    }

    /**
     * Get the http client.
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient()
    {
        if (! $this->httpClient instanceof HttpClient) {
            $this->httpClient = new HttpClient;
        }

        return $this->httpClient;
    }

    /**
     * Any unhandled methods will be sent to a new Message instance.
     *
     * @param  string  $name
     * @param  array  $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        $message = $this->createMessage();

        return call_user_func_array([$message, $name], $args);
    }
}
