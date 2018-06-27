<?php

namespace whm\Crawler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

interface Filter
{
    public function isFiltered(UriInterface $currentUri, UriInterface $startUri);

    public function isResponseFiltered(ResponseInterface $response, UriInterface $startUri);
}
