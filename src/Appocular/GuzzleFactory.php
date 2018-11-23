<?php

namespace Rorschach\Appocular;

use GuzzleHttp\Client;

class GuzzleFactory
{
    /**
     * Return Guzzle Client instance.
     *
     * @return Client
     */
    public function get()
    {
        return new Client(['base_uri' => 'http://assessor.ogle.docker/']);
    }
}
