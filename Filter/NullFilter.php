<?php

namespace whm\Crawler\Filter;

use Psr\Http\Message\UriInterface;
use whm\Crawler\Filter;

class NullFilter implements Filter
{
    public function isFiltered(UriInterface $currentUri, UriInterface $startUri)
    {
        return false;
    }
}
