<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Http\Response;

/**
 * Abstract Response
 */
abstract class AbstractResponse implements ResponseInterface
{
    protected $headers = [];
    protected $headerNames = [];
    protected $streams = [];

    /**
     * Init response with the body type
     *
     * @param type $bodyType
     */
    public function __construct($bodyType = 'text/html')
    {
        $this->setContentType($bodyType);
    }

    /**
     * Method that add body to the response
     *
     * @param mixed $content
     * @return void
     */
    public function writeStream($content, $stremId = 'main')
    {
        $this->streams[$stremId] = $content;
    }

    public function clearCache()
    {
        $this->withHeader("Expires", "Tue, 01 Jan 2000 00:00:00 GMT");
        $this->withHeader("Last-Modified", gmdate("D, d M Y H:i:s") . " GMT");
        $this->withHeader("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");
        $this->withHeader("Cache-Control", "post-check=0, pre-check=0", false);
        $this->withHeader("Pragma", "no-cache");
    }

    public function get()
    {
        $this->sendHeader();
        return implode('', $this->streams);
    }

    /**
     * Send header location to browser
     *
     * @param string $url
     */
    public function go($url)
    {
        header('Location: '.$url);
    }

    /**
     * Reset streams.
     *
     * @param mixed $part
     */
    public function resetStreams()
    {
        $this->streams = [];
    }

    /**
     * Set body type of the response
     *
     * @param string $type
     */
    public function setContentType($type)
    {
        $this->withHeader('Content-Type', $type);
    }

    /**
     * Buffering of header
     *
     * @param string $key
     * @param string $value
     */
    public function withHeader($key, $value)
    {
        $caseInsesitiveKey = $this->caseInsesitiveKey($key);
        $this->headerNames[$caseInsesitiveKey] = $key;
        $this->headers[$key] = $value;
    }

    public function withAddedHeader($key, $value)
    {
        if (!$this->hasHeader($key)) {
            $this->withHeader($key, $value);
            return;
        }
        $originalKey = $this->headerNames[$this->caseInsesitiveKey($key)];
        $this->headers[$originalKey] .= ', '.$value;
    }

    protected function caseInsesitiveKey($key)
    {
        return strtolower($key);
    }

    /**
     * Check if key exists in header repository
     *
     * @param type $key key to search
     * @return bool
     */
    public function hasHeader($key) : bool
    {
        return array_key_exists($this->caseInsesitiveKey($key), $this->headerNames);
    }

    /**
     * Return the header line by key
     *
     * @param type $key
     * @return string
     */
    public function getHeaderLine($key) : ?string
    {
        return $this->hasHeader($key) ? $this->headers[$key] : null;
    }

    /**
     *  return specific header values (in array) if exists else return empty array
     *
     * @param type $key
     * @return array
     */
    public function getHeader($key)
    {
        return $this->hasHeader($key) ? explode(', ',$this->headers[$key]) : [];
    }

    /**
     * Return all headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Method for build response string
     * @abstract
     */
    abstract public function __toString();

    abstract public function getBody();
}
