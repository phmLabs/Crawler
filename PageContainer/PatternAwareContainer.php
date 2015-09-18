<?php

namespace whm\Crawler\PageContainer;

use Psr\Http\Message\UriInterface;

class PatternAwareContainer implements PageContainer
{
    private $allElements = [];

    private $newPatternElements = [];
    private $knownPatternElements = [];

    private $knownPattern = [];

    private $registeredPatterns = [];

    public function registerPattern($name, $regExPattern)
    {
        $this->registeredPatterns[$name] = $regExPattern;
    }

    public function getAllElements()
    {
        return array_merge($this->newPatternElements, $this->knownPatternElements);
    }

    /**
     * @param UriInterface $uri
     */
    public function push(UriInterface $uri)
    {
        $uriString = (string)$uri;

        if (!array_key_exists($uriString, $this->allElements)) {
            $this->allElements[$uriString] = true;

            $pattern = "";
            foreach ($this->registeredPatterns as $name => $regExPattern) {
                if (preg_match($regExPattern, $uriString)) {
                    $pattern = $name;
                    break;
                }
            }

            if (!$pattern) {
                $analyzer = new PatternAnalyzer($uri);
                $pattern = $analyzer->getPattern();
            }

            if (array_key_exists($pattern, $this->knownPattern)) {
                $this->knownPatternElements[] = $uri;
            } else {
                $this->newPatternElements[] = $uri;
                $this->knownPattern[$pattern] = true;
            }
        }
    }

    public function getNextElement()
    {
        if (count($this->newPatternElements) > 0) {
            return array_pop($this->newPatternElements);
        } else {
            return array_pop($this->knownPatternElements);
        }
    }

    public function pop($count = 1)
    {
        $elements = [];
        for ($i = 0; $i < $count; ++$i) {
            $element = $this->getNextElement();
            if (!is_null($element)) {
                $elements[] = $element;
            }
        }
        return $elements;
    }
}
