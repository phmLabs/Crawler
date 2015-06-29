<?php

namespace whm\Crawler;

use Ivory\HttpAdapter\Message\Request;
use Ivory\HttpAdapter\PsrHttpAdapterInterface;
use Psr\Http\Message\UriInterface;
use whm\Html\Document;

class Crawler
{
    private $httpClient;

    private $pageContainer;

    public function __construct(PsrHttpAdapterInterface $httpClient, UriInterface $startUri)
    {
        $this->httpClient = $httpClient;
        $this->pageContainer = new PageContainer();
        $this->pageContainer->push($startUri);
    }

    public function next()
    {
        $uris = $this->pageContainer->pop(1);

        if (empty($uris)) {
            return false;
        }

        $uri = $uris[0];

        var_dump($uri);

        $requests[] = new Request($uri, 'GET', 'php://memory', ['Accept-Encoding' => 'gzip'], []);

        $reponse = $this->httpClient->sendRequests($requests);

        $document = new Document((string) $reponse->getBody());

        if ($reponse->hasHeader('Content-Type')) {
            $contentTypeElements = explode(';', $reponse->getHeader('Content-Type')[0]);
            $contentType = array_shift($contentTypeElements);
        }

        if ($contentType !== "text/html") {
            return $this->next();
        }

        $elements = $document->getDependencies($uri);

        foreach ($elements as $element) {
            $this->pageContainer->push($element);
        }

        return ["uri" => $uri, "response" => $reponse];
    }
}
