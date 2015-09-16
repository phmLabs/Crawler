<?php

namespace whm\Crawler\PageContainer;

use Psr\Http\Message\UriInterface;

class SimpleContainer implements PageContainer
{
    private $currentElements = [];
    private $allElements = [];

    public function getAllElements()
    {
        return $this->allElements;
    }

    public function push(UriInterface $uri)
    {
        $uriString = (string) $uri;
        if (!array_key_exists($uriString, $this->allElements)) {
            $this->allElements[$uriString] = true;
            array_unshift($this->currentElements, $uri);
        }
    }

    public function pop($count = 1)
    {
        $elements = [];
        for ($i = 0; $i < $count; ++$i) {
            $element = array_pop($this->currentElements);
            if (!is_null($element)) {
                $elements[] = $element;
            }
        }
        return $elements;
    }
}
