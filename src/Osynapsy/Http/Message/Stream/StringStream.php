<?php
namespace Osynapsy\Http\Message\Stream;

/**
 * Description of String
 *
 * @author Pietro Celeste <pietro.celeste@gmail.com>
 */
class StringStream
{
    protected $stream = '';
    protected $streamLength = 0;
    protected $position = 0;
    //Default operation w is necessary for init stream;
    protected $operations = 'w';

    public function __construct($stream = '', $operations = 'a')
    {
        $this->write($stream);
        $this->operations = $operations;
        $this->rewind();
    }

    public function eof()
    {
        return ($this->position === $this->streamLength);
    }

    public function end()
    {
        $this->position = $this->streamLength;
    }

    public function isReadable()
    {
        return (strpos($this->operations, 'r') !== false || strpos($this->operations, 'a') !== false);
    }

    public function isSeekable()
    {
        return true;
    }

    public function isWriteable()
    {
        return (strpos($this->operations, 'w') !== false || strpos($this->operations, 'a') !== false);
    }

    public function getContent()
    {
        return $this->read($this->streamLength - $this->position);
    }

    public function postpend($text, $keysearch)
    {
        $keyposition = $this->search($keysearch);
        if ($keyposition === false) {
            return false;
        }
        $keyposition += strlen($keysearch);
        $this->seek($keyposition);
        $this->write($text);
    }

    public function prepend($text, $keysearch)
    {
        $keyposition = $this->search($keysearch);
        if ($keyposition === false) {
            return false;
        }
        $this->seek($keyposition);
        $this->write($text);
    }

    public function read($requestLength)
    {
        if ($this->isReadable() === false) {
            return;
        }
        $position = $this->position;
        $readLength = min($this->streamLength - $position, $requestLength);
        $this->position += $readLength;
        return substr($this->stream, $position, $readLength);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function seek($position)
    {
        if ($this->isSeekable() === false) {
            return;
        }
        $this->position = min($position, $this->streamLength);
    }

    public function search($key)
    {
        return strpos($this->stream, $key, $this->position);
    }

    public function tell()
    {
        return $this->position;
    }

    public function write($text)
    {
        if ($this->isWriteable() === false) {
            return;
        }
        $this->stream = substr_replace($this->stream, $text, $this->tell(), 0);
        $this->position += strlen($text);
        $this->streamLength = strlen($this->stream);
    }

    public function __toString()
    {
        return $this->stream;
    }
}
