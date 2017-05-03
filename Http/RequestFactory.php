<?php

namespace whm\Crawler\Http;

use GuzzleHttp\Psr7\Request;

class RequestFactory
{
    private static $standardHeaders = [];

    public static function addStandardHeader($key, $value)
    {
        self::$standardHeaders[$key] = $value;
    }

    public static function getRequest($uri = null, $method = null, $body = 'php://memory', array $headers = array(), array $parameters = array())
    {
        $headers = array_merge(self::$standardHeaders, $headers);
        return new Request($method, $uri, $headers, $body, $parameters);
    }
}
