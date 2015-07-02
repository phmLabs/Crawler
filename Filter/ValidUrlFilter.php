<?php

namespace whm\Crawler\Filter;

use Psr\Http\Message\UriInterface;
use whm\Crawler\Filter;

class ValidUrlFilter implements Filter
{
    public function isFiltered(UriInterface $currentUri, UriInterface $startUri)
    {
        return (!filter_var((string) $currentUri, FILTER_VALIDATE_URL));
    }
}
