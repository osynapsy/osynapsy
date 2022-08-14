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
    const VALID_PROTOCOL_VERSION = ['1.0', '1.1', '2.0', '2'];
    protected $protocol = '1.1';
    protected $headers = [];
    protected $headerNames = [];
    protected $bodyStream;

    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    protected function setProtocolVersion($protocolVersion)
    {
        $this->validateProtocolVersion($protocolVersion);
        $this->protocol = $protocolVersion;
    }

    public function withProtocolVersion($protocolVersion)
    {
        $result = clone $this;
        $result->setProtocolVersion($protocolVersion);
        return $result;
    }

    protected function validateProtocolVersion($protocolVersion)
    {
        if (!in_array($protocolVersion, self::VALID_PROTOCOL_VERSION)) {
            throw new InvalidArgumentException(
                sprtinf(
                    'Invalid HTTP version. Must be one of: %s',
                    implode(', ', self::VALID_PROTOCOL_VERSION)
                )
            );
        }
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

    protected function setHeaders(array $headers)
    {
        foreach($headers as $name => $value) {
            $caseInsesitiveKey = $this->caseInsesitiveKey($name);
            $this->headerNames[$caseInsesitiveKey] = $name;
            $values = is_array($value) ? $value : [$value];
            $this->headers[$name] = array_merge($this->headers[$name] ?? [], $values);
        }
    }

    protected function setBody(StreamInterface $stream)
    {
        $this->bodyStream = $stream;
    }

    public function withBody(StreamInterface $stream)
    {
        if ($stream === $this->bodyStream) {
            return $this;
        }
        $result = clone $this;
        $result->setBody($stream);
        return $result;
    }

    protected function caseInsesitiveKey($key)
    {
        return strtolower($key);
    }
}
