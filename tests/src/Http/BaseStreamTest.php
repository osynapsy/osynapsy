<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Osynapsy\Http\Psr7\Message\Stream\Base as BaseStream;

/**
 * Description of StringStreamTest
 *
 * @author Pietro Celeste <pietro.celeste@gmail.com>
 */
class BaseStreamTest extends TestCase
{
    protected function streamFactory($initContent = null)
    {
        $stream = new BaseStream(fopen('php://memory', 'r+'));
        if (!empty($initContent)) {
            $stream->write($initContent);
            $stream->rewind();
        }
        return $stream;
    }

    public function testStream(): void
    {
        $string = 'prova';
        $stream = $this->streamFactory($string);
        $this->assertEquals((string) $stream, $string);
    }

    public function testStreamIsReadable(): void
    {
        $stream = $this->streamFactory();
        $this->assertTrue($stream->isReadable());
    }

    public function testStreamRead(): void
    {
        $string = 'test the StringStream';
        $stream = $this->streamFactory($string);
        $this->assertEquals('tes', $stream->read(3));
    }

    public function testStreamRead2(): void
    {
        $string = 'test the StringStream';
        $stream = $this->streamFactory($string);
        $stream->read(3);
        $this->assertEquals('t t', $stream->read(3));
    }

    public function testGetContents(): void
    {
        $string = 'test the StringStream';
        $stream = $this->streamFactory($string);
        $stream->read(5);
        $this->assertEquals($stream->getContents(), 'the StringStream');
    }

    public function testStreamIsWritable(): void
    {
        $string1 = 'test the StringStream';
        $stream = $this->streamFactory($string1);
        $this->assertTrue($stream->isWritable());
    }

    public function testWrite(): void
    {
        $string1 = 'test the StringStream';
        $string2 = ' and it method write';
        $stream = $this->streamFactory($string1);
        $stream->end();
        $stream->write($string2);
        $stream->rewind();
        $this->assertEquals($stream->getContents(), $string1.$string2);
    }

    public function testSteramIsEof(): void
    {
        $string1 = 'test the StringStream';
        $stream = $this->streamFactory($string1);
        $stream->read(9);
        $stream->read(13);
        $this->assertTrue($stream->eof());
    }

    public function testStreamSeek(): void
    {
        $string1 = 'test the StringStream';
        $stream = $this->streamFactory($string1);
        $stream->seek(5);
        $this->assertEquals($stream->getContents(), 'the StringStream');
    }

    public function testStreamTell(): void
    {
        $string1 = 'test the StringStream';
        $stream = $this->streamFactory($string1);
        $stream->seek(5);
        $stream->read(5);
        $this->assertEquals(10, $stream->tell());
    }

    public function testStreamSearch(): void
    {
        $string1 = 'test the StringStream';
        $stream = $this->streamFactory($string1);
        $this->assertEquals(5, $stream->search('the'));
    }

    public function testStreamPrepend(): void
    {
        $stream = $this->streamFactory('<html>{{main}}</html>');
        $stream->prepend('test prepend', '{{main}}');
        $this->assertEquals('<html>test prepend{{main}}</html>', (string) $stream);
    }

    public function testStreamPostpend(): void
    {
        $stream = $this->streamFactory('<html>{{main}}</html>');
        $stream->postpend('test postpend', '{{main}}');
        $this->assertEquals('<html>{{main}}test postpend</html>', (string) $stream);
    }
}
