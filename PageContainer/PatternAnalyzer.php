<?php

namespace whm\Crawler\PageContainer;

use whm\Html\Uri;

class PatternAnalyzer
{
    /**
     * The uri that will be analyzed
     *
     * @var Uri
     */
    private $uri;

    /**
     * Hand over the uri that has to be analyzed
     *
     * @param Uri $uri
     */
    public function __construct(Uri $uri)
    {
        $this->uri = $uri;
    }

    /**
     * Returns the file type of the files represented by the given url. If no file
     * extension is set html is assumed.
     *
     * @return string
     */
    private function getFileType()
    {
        $uriString = $this->uri->getPath();

        if (substr($uriString, strlen($uriString) - 1) == "/") {
            return 'html';
        }

        $pathParts = pathinfo($uriString);

        if (!array_key_exists('extension', $pathParts)) {
            return 'html';
        } else {
            $extension = $pathParts['extension'];
            if ($extension === "") {
                return 'html';
            } else {
                // @todo if ? and # are set this function will not work correct
                $pos = max((int)strpos($extension, "?"), (int)strpos($extension, "#"));
                if ($pos === 0) {
                    $pos = strlen($extension);
                }
                return substr($extension, 0, $pos);
            }
        }
    }

    /**
     * Returns a pattern representing an uri. It can be used to check if two urls have the same
     * equivalence class.
     *
     * @return string
     */
    public function getPattern()
    {
        // file type
        $pattern = $this->getFileType() . ':';

        // schema (http, https)
        $pattern .= $this->uri->getScheme() . ':';

        // host
        $pattern .= $this->uri->getHost() . ':';

        $path = $this->uri->getPath() . '?' . $this->uri->getQuery();

        $pathNew = preg_replace("^[a-f0-9]{32}^", "<h>", $path);
        $pathNew = preg_replace("^[a-z\-\_]{1,}^i", "<s>", $pathNew);
        $pathNew = preg_replace("^[0-9]{1,}^", "<i>", $pathNew);

        $pattern .= $pathNew;

        return $pattern;
    }
}
