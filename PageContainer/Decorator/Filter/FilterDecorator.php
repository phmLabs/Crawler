<?php

namespace whm\Crawler\PageContainer\Decorator\Filter;

use Psr\Http\Message\UriInterface;
use whm\Crawler\PageContainer\Decorator\Filter\Filter\Filter;
use whm\Crawler\PageContainer\PageContainer;

class FilterDecorator implements PageContainer
{
    private $pageContainer;

    /** @var Filter[] */
    private $filters = [];

    public function __construct(PageContainer $pageContainer)
    {
        $this->pageContainer = $pageContainer;
    }

    public function addFilter(Filter $filter)
    {
        $this->filters[] = $filter;
    }

    public function getAllElements()
    {
        return $this->pageContainer->getAllElements();
    }

    public function push(UriInterface $uri)
    {
        foreach ($this->filters as $filter) {
            if ($filter->isFiltered($uri)) {
                return false;
            }
        }

        return $this->pageContainer->push($uri);
    }

    public function pop($count = 1)
    {
        return $this->pageContainer->pop($count);
    }

}