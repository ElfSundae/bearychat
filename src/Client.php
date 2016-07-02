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
     * @param  array  $messageDefaults  see MessageDefaults
     * @param  \GuzzleHttp\Client  $httpClient
     */
    public function __construct($webhook, $messageDefaults = [], $httpClient = null)
    {
        $this->webhook = $webhook;

        $this->configureMessageDefaults($messageDefaults);

        $this->httpClient = $httpClient;
    }

    /**
     * All undefined methods will be sent to a new Message instance.
     *
     * @param  string  $name
     * @param  array  $args
     * @return \ElfSundae\BearyChat\Message
     */
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

    /**
     * Configure message defaults.
     *
     * @param  array  $messageDefaults
     */
    protected function configureMessageDefaults(array $messageDefaults)
    {
        $defaults = [
            MessageDefaults::MARKDOWN => true,
        ];

        $this->messageDefaults = $messageDefaults + $defaults;
    }

    /**
     * Retrieve message defaults.
     *
     * @param  string|null  $option
     * @return mixed
     */
    public function getMessageDefaults($option = null)
    {
        return is_null($option) ?
            $this->messageDefaults :
            (isset($this->messageDefaults[$option]) ?  $this->messageDefaults[$option] : null);
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
        if ($payload = $this->getPayload($message)) {

            $response = $this->getHttpClient()->post(
                $this->getWebhook(),
                ['body' => $payload]
            );

            return (200 === $response->getStatusCode());
        }

        return false;
    }

    /**
     * Get the payload from a object.
     *
     * @param  mixed  $message
     * @return string
     */
    protected function getPayload($message)
    {
        if (is_object($message) && is_callable([$message, 'toArray'])) {
            $message = $message->toArray();
        }

        if ($message && !is_string($message)) {
            $message = json_encode($message);
        }

        return $message;
    }
}
