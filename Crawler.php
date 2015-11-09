<?php

namespace whm\Crawler;

use Ivory\HttpAdapter\Message\Request;
use Ivory\HttpAdapter\MultiHttpAdapterException;
use Ivory\HttpAdapter\PsrHttpAdapterInterface;
use Psr\Http\Message\UriInterface;
use whm\Crawler\PageContainer\PageContainer;
use whm\Html\Document;
use whm\Html\Uri;

class Crawler
{
    private $httpClient;
    private $startUri;
    private $pageContainer;
    private $parallelReqeusts;

    private $responseCache;

    private $comingFrom = array();

    /**
     * @var Filter[]
     */
    private $filters = array();

    public function __construct(PsrHttpAdapterInterface $httpClient, PageContainer $container, UriInterface $startUri, $parallelRequests = 5)
    {
        $this->httpClient = $httpClient;
        $this->pageContainer = $container;
        $this->pageContainer->push($startUri);
        $this->startUri = $startUri;
        $this->parallelReqeusts = $parallelRequests;
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

            if (empty($urls)) {
                return false;
            }

            $requests = array();

            foreach ($urls as $url) {
                if (!$this->isFiltered($url)) {
                    $requests[] = new Request($url, 'GET', 'php://memory', ['Accept-Encoding' => 'gzip'], []);
                }
            }

            if (empty($requests)) {
                return $this->next();
            }
            try {
                $this->responseCache = $this->httpClient->sendRequests($requests);
            } catch (MultiHttpAdapterException $e) {
                $exceptions = $e->getExceptions();
                $errorMessages = "";
                foreach ($exceptions as $exception) {

                    // @fixme this must be part of the http client
                    $message = $exception->getMessage();
                    if (strpos($message, "An error occurred when fetching the URI") === 0) {
                        $url = substr($message, "41", strpos($message, '"', 41) - 41);
                        if (strpos($url, '/') === 0) {
                            $this->pageContainer->push(new Uri($this->startUri->getScheme() . '://' . $this->startUri->getHost() . $url));
                        }
                    } else {
                        $errorMessages .= $exception->getMessage() . "\n";
                    }
                }
                if ($errorMessages != "") {
                    throw new \RuntimeException($errorMessages);
                }
            }
        }

        if (empty($this->responseCache)) {
            return $this->next();
        }

        $response = array_pop($this->responseCache);

        $document = new Document((string)$response->getBody(), true);

        if ($response->hasHeader('Content-Type')) {
            $contentTypeElements = explode(';', $response->getHeader('Content-Type')[0]);
            $contentType = array_shift($contentTypeElements);

            if ($contentType === "text/html") {
                $elements = $document->getUnorderedDependencies($response->getUri());

                foreach ($elements as $element) {
                    $urlString = $this->createCleanUriString($element);
                    if (!array_key_exists($urlString, $this->comingFrom)) {
                        $this->comingFrom[$urlString] = $response->getUri();
                    }
                    $this->pageContainer->push($element);
                }
            }
        }

        return $response;
    }

    /**
     * "Repair" the uri as a browser would do it
     *
     * @param Uri $uri
     * @return string
     */
    private function createCleanUriString(Uri $uri)
    {
        return trim((string)$uri);
    }

    /**
     * @param UriInterface $uri
     * @return mixed
     */
    public function getComingFrom(UriInterface $uri)
    {
        if (array_key_exists((string)$uri, $this->comingFrom)) {
            return $this->comingFrom[(string)$uri];
        } else {
            return "";
        }

    }
}
