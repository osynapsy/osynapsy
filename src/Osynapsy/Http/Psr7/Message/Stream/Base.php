<?php
namespace Osynapsy\Http\Psr7\Message\Stream;

use Psr\Http\Message\StreamInterface;

/**
 * Description of Base
 *
 * @author pietro
 */
class Base implements StreamInterface
{
    protected $stream;
    protected $metadata;

    public function __construct($stream)
    {
        $this->stream = $stream;
        $this->metadata = stream_get_meta_data($this->stream);
    }

    public function close()
    {
        fclose($this->stream);
    }

    public function detach()
    {
        $this->stream = null;
    }

    public function eof()
    {
        return feof($this->stream);
    }

    public function end()
    {
        fseek($this->stream, 0, SEEK_END);
    }

    public function getContents()
    {
        return stream_get_contents($this->stream);
    }

    public function getMetadata($key = null)
    {
        return is_null($key) ? $this->metadata : $this->metadata[$key];
    }

    public function getSize()
    {
        return fstat($this->stream)['size'];
    }

    public function isReadable()
    {
        return in_array($this->getMetadata('mode'), ['r', 'r+', 'w+', 'w+b']);
    }

    public function isWritable()
    {
        return in_array($this->getMetadata('mode'), ['w', 'r+', 'w+', 'w+b']);
    }

    public function isSeekable(): bool
    {
        return $this->getMetadata('seekable') ? true : false;
    }

    public function postpend($text, $keysearch)
    {
        $keyposition = $this->search($keysearch);
        if ($keyposition === false) {
            return false;
        }
        $keyposition += strlen($keysearch);
        $this->rewind();
        $newcontent = substr_replace($this->getContents(), $text, $keyposition, 0);
        $this->rewind();
        $this->write($newcontent);
    }

    public function prepend($text, $keysearch)
    {
        $keyposition = $this->search($keysearch);
        if ($keyposition === false) {
            return false;
        }
        $this->rewind();
        $newcontent = substr_replace($this->getContents(), $text, $keyposition, 0);
        $this->rewind();
        $this->write($newcontent);
    }

    public function read($requestLength)
    {
        return fread($this->stream, $requestLength);
    }

    public function rewind()
    {
        $this->seek(0);
    }

    public function search($keysearch)
    {
        $currentPosition = $this->tell();
        $this->rewind();
        $result = strpos($this->getContents(), $keysearch);
        $this->seek($currentPosition);
        return $result;
    }

    public function seek($position, $whence = \SEEK_SET)
    {
        fseek($this->stream, $position, $whence);
    }

    public function tell()
    {
        return ftell($this->stream);
    }

    public function write($text)
    {
        fwrite($this->stream, $text);
    }

    public function __toString()
    {
        $this->rewind();
        return $this->getContents();
    }
}
