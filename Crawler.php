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
    private $parallelReqeusts;

    private $responseCache;

    /**
     * @var Filter[]
     */
    private $filters = array();

    public function __construct(PsrHttpAdapterInterface $httpClient, UriInterface $startUri, $paralellRequests = 5)
    {
        $this->httpClient = $httpClient;
        $this->pageContainer = new PageContainer();
        $this->pageContainer->push($startUri);
        $this->startUri = $startUri;
        $this->parallelReqeusts = $paralellRequests;
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
        if (count($this->responseCache) == 0) {
            $urls = $this->pageContainer->pop($this->parallelReqeusts);

            if(empty($urls)) {
                return false;
            }

            $requests = array();

            foreach ($urls as $url) {
                if (!$this->isFiltered($url)) {
                    $requests[] = new Request($url, 'GET', 'php://memory', ['Accept-Encoding' => 'gzip'], []);
                }
            }

            if(empty($requests)) {
                return $this->next();
            }

            $this->responseCache = $this->httpClient->sendRequests($requests);
        }

        $response = array_pop($this->responseCache);

        $document = new Document((string) $response->getBody());

        if ($response->hasHeader('Content-Type')) {
            $contentTypeElements = explode(';', $response->getHeader('Content-Type')[0]);
            $contentType = array_shift($contentTypeElements);

            if ($contentType === "text/html") {
                $elements = $document->getUnorderedDependencies($response->getUri());

                foreach ($elements as $element) {
                    $this->pageContainer->push($element);
                }
            }
        }

        return $response;
    }
}
