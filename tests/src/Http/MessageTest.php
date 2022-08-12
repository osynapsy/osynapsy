<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Osynapsy\Http\Psr7\Message\Message as HttpMessage;

/**
 * Description of StringStreamTest
 *
 * @author Pietro Celeste <pietro.celeste@gmail.com>
 */
class MessageTest extends TestCase
{
    public function messageFactory()
    {
        return new HttpMessage();
    }

    public function testFactory()
    {
        $this->assertNotEmpty($this->messageFactory());
    }

    public function testSetProtocol()
    {
        $protocolVersion = '1.0';
        $message = $this->messageFactory();
        $newMessage = $message->withProtocolVersion($protocolVersion);
        $this->assertEquals($newMessage->getProtocolVersion(), $protocolVersion);
    }

    public function testSetHeader()
    {
        $header = 'test';
        $headerValue = 'utf-8';
        $message = $this->messageFactory();
        $this->assertNotEquals($message, $message->withHeader($header, $headerValue));
    }

    public function testGetHeader()
    {
        $header = 'test';
        $headerValue = 'utf-8';
        $message = $this->messageFactory();
        $newMessage = $message->withHeader($header, $headerValue);
        $this->assertEquals($newMessage->getHeader($header), [$headerValue]);
        $this->assertEquals($newMessage->getHeader('tEST'), [$headerValue]);
    }

    public function testGetHeaderLine()
    {
        $header = 'test';
        $headerValue1 = 'utf-8';
        $headerValue2 = 'latin-1';
        $message = $this->messageFactory();
        $newMessage = $message->withHeader($header, $headerValue1)->withAddedHeader($header, $headerValue2);
        $this->assertEquals($newMessage->getHeaderLine($header), sprintf('%s, %s',$headerValue1, $headerValue2));
    }
}
