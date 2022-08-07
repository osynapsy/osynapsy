<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Osynapsy\Http\Message\Stream\StringStream;

/**
 * Description of StringStreamTest
 *
 * @author Pietro Celeste <pietro.celeste@gmail.com>
 */
class StringStreamTest extends TestCase
{
    public function testStream(): void
    {
        $string = 'prova';
        $stream = new StringStream($string, 'w');
        $this->assertEquals((string) $stream, $string);
    }

    public function testStreamIsReadable(): void
    {
        $string1 = 'test the StringStream';
        $stream1 = new StringStream($string1, 'r');
        $this->assertTrue($stream1->isReadable());
        $stream2 = new StringStream($string1, 'a');
        $this->assertTrue($stream2->isReadable());
    }

    public function testStreamRead(): void
    {
        $string = 'test the StringStream';
        $stream = new StringStream($string);
        $result = $stream->read(3);
        $this->assertEquals('tes', $result);
    }

    public function testStreamRead2(): void
    {
        $string = 'test the StringStream';
        $stream = new StringStream($string);
        $stream->read(3);
        $str2 = $stream->read(3);
        $this->assertEquals('t t', $str2);
    }

    public function testGetContents(): void
    {
        $string = 'test the StringStream';
        $stream = new StringStream($string);
        $stream->read(5);
        $this->assertEquals($stream->getContent(), 'the StringStream');
    }

    public function testStreamIsWritable(): void
    {
        $string1 = 'test the StringStream';
        $stream = new StringStream($string1, 'w');
        $this->assertTrue($stream->isWriteable());
    }

    public function testWrite(): void
    {
        $string1 = 'test the StringStream';
        $string2 = ' and it method write';
        $stream = new StringStream($string1);
        $stream->write($string2);
        $this->assertEquals($stream->getContent(), $string1.$string2);
    }

    public function testSteramIsEof(): void
    {
        $string1 = 'test the StringStream';
        $stream = new StringStream($string1, 'r');
        $stream->read(9);
        $stream->read(13);
        $this->assertTrue($stream->eof());
    }

    public function testStreamSeek(): void
    {
        $string1 = 'test the StringStream';
        $stream = new StringStream($string1, 'r');
        $stream->getContent();
        $stream->seek(5);
        $this->assertEquals($stream->getContent(), 'the StringStream');
    }

    public function testStreamTell(): void
    {
        $string1 = 'test the StringStream';
        $stream = new StringStream($string1, 'r');
        $stream->seek(5);
        $stream->read(5);
        $this->assertEquals(10, $stream->tell());
    }

    protected function debug($result)
    {
        var_dump($result);
        ob_flush();
    }
}
