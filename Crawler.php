<?php

namespace whm\Crawler;

use Ivory\HttpAdapter\Message\Request;
use Ivory\HttpAdapter\PsrHttpAdapterInterface;
use Psr\Http\Message\UriInterface;
use whm\Html\Document;

class Crawler
{
    private $httpClient;
    private $startUri;
    private $pageContainer;

    /**
     * @var Filter[]
     */
    private $filters = array();

    public function __construct(PsrHttpAdapterInterface $httpClient, UriInterface $startUri)
    {
        $this->httpClient = $httpClient;
        $this->pageContainer = new PageContainer();
        $this->pageContainer->push($startUri);
        $this->startUri = $startUri;
    }

    public function addFilter(Filter $filter)
    {
        $this->filters[] = $filter;
    }

    private function isFiltered(UriInterface $uri)
    {
        foreach ($this->filters as $filter) {
            if ($filter->isFiltered($uri, $this->startUri)) {
                return true;
            }
        }
        return false;
    }

    public function next()
    {
        $uris = $this->pageContainer->pop(1);

        if (empty($uris)) {
            return false;
        }

        $uri = $uris[0];

        if ($this->isFiltered($uri)) {
            return $this->next();
        }

        $requests[] = new Request($uri, 'GET', 'php://memory', ['Accept-Encoding' => 'gzip'], []);

        $reponses = $this->httpClient->sendRequests($requests);

        $document = new Document((string) $reponses[0]->getBody());

        if ($reponses[0]->hasHeader('Content-Type')) {
            $contentTypeElements = explode(';', $reponses[0]->getHeader('Content-Type')[0]);
            $contentType = array_shift($contentTypeElements);

            if ($contentType === "text/html") {
                $elements = $document->getUnorderedDependencies($uri);

                foreach ($elements as $element) {
                    $this->pageContainer->push($element);
                }
            }
        }

        return $reponses[0];
    }
}
