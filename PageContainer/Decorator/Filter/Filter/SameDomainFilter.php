<?php

namespace whm\Crawler\PageContainer\Decorator\Filter\Filter;

use Psr\Http\Message\UriInterface;

class SameDomainFilter implements Filter
{
    private $uri;

    /**
     * SubstringFilter constructor.
     * @param array $substrings
     */
    public function __construct(UriInterface $uri)
    {
        $this->uri = $uri;
    }

    public function isFiltered(UriInterface $uri)
    {
        return $uri->getHost() != $this->uri->getHost();
    }
}
