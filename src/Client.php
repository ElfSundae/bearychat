<?php

namespace ElfSundae\BearyChat;

use GuzzleHttp\Client as GuzzleClient;

class Client
{

    /**
     * The BearyChat incoming webhook URL.
     *
     * @var string
     */
    protected $webhook;

    /**
     * The Guzzle HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $guzzle_client;

}
