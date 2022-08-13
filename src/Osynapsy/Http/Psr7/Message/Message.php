<?php
namespace Osynapsy\Http\Psr7\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Description of Message
 *
 * @author Pietro Celeste <pietro.celeste@gmail.com>
 */
class Message implements MessageInterface
{
    private $protocol = '1.1';
    protected $headers = [];
    protected $headerNames = [];
    protected $bodyStream;

    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    public function withProtocolVersion($protocol)
    {
        $result = clone $this;
        $result->protocol = $protocol;
        return $result;
    }

    public function withHeader($key, $value)
    {
        $caseInsesitiveKey = $this->caseInsesitiveKey($key);
        $result = clone $this;
        $result->headerNames[$caseInsesitiveKey] = $key;
        $result->headers[$key] = is_array($value) ? $value : [$value];
        return $result;
    }

    public function withAddedHeader($key, $value)
    {
        $caseInsesitiveKey = $this->caseInsesitiveKey($key);
        $values = is_array($value) ? $value : [$value];
        $result = clone $this;
        if (!$this->hasHeader($key)) {
            $result->headerNames[$caseInsesitiveKey] = $key;
        }
        $result->headers[$key] = array_merge($this->headers[$key] ?? [], $values);
        return $result;
    }

    public function withoutHeader($name)
    {
        if (!$this->hasHeader($name)) {
            return $this;
        }
        unset($this->headers[$name]);
        return clone $this;
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
     * Return the header values by key
     *
     * @param type $key
     * @return array
     */
    public function getHeader($key) : ?array
    {
        return $this->hasHeader($key) ? $this->headers[$this->caseInsesitiveKey($key)] : null;
    }

    /**
     * Return associative array of headers
     *
     * @param type $key
     * @return array
     */
    public function getHeaders() : array
    {
        return $this->headers;
    }

    /**
     * Return the header line by key
     *
     * @param type $key
     * @return string
     */
    public function getHeaderLine($key) : ?string
    {
        return $this->hasHeader($key) ? implode(', ', $this->headers[$key]) : null;
    }

    public function getBody()
    {
        return $this->bodyStream;
    }

    public function withBody(StreamInterface $body)
    {
        if ($body === $this->bodyStream) {
            return $this;
        }
        $result = clone $this;
        $result->bodyStream = $body;
        return $result;
    }

    protected function caseInsesitiveKey($key)
    {
        return strtolower($key);
    }
}
