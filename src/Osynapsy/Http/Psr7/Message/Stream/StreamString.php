<?php
namespace Osynapsy\Http\Psr7\Message\Stream;

/**
 * Description of String
 *
 * @author Pietro Celeste <pietro.celeste@gmail.com>
 */
class StreamString extends Base
{
    public function __construct(string $stream = '', $operations = 'r+')
    {
        $this->stream = fopen('php://memory', $operations);
        $this->write($stream);
        $this->rewind();
    }
}
