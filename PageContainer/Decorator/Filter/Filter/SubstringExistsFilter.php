<?php

namespace whm\Crawler\PageContainer\Decorator\Filter\Filter;

use Psr\Http\Message\UriInterface;

class SubstringExistsFilter implements Filter
{
    private $substrings = [];

    /**
     * SubstringFilter constructor.
     * @param array $substrings
     */
    public function __construct(array $substrings)
    {
        $this->substrings = $substrings;
    }

    public function isFiltered(UriInterface $uri)
    {
        foreach ($this->substrings as $substring) {
            if (strpos((string)$uri, $substring) === false) {
                return true;
            }
        }
        return false;
    }
}