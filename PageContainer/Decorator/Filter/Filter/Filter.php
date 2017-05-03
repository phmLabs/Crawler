<?php

namespace whm\Crawler\PageContainer\Decorator\Filter\Filter;

use Psr\Http\Message\UriInterface;

interface Filter
{
    public function isFiltered(UriInterface $uri);
}