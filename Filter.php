<?php

namespace whm\Crawler;

use Psr\Http\Message\UriInterface;

interface Filter
{
    public function isFiltered(UriInterface $currentUri, UriInterface $startUri);
}
