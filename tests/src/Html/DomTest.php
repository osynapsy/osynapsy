<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Osynapsy\Html\Dom;
use Osynapsy\Html\Tag;

final class DomTest extends TestCase
{
    public function testDom(): void
    {
        $this->assertEquals(Dom::exists('testId'), false);
    }

    public function testAddElementToDom(): void
    {
        Dom::addElement(new Tag('div', 'testId'));
        $this->assertEquals(Dom::exists('testId'), true);
    }

    public function testGetElementFromDom(): void
    {
        $div = new Tag('div', 'testId');
        Dom::addElement($div);
        $this->assertEquals(Dom::getById('testId'), $div);
    }

    public function testGetElementNotInDom(): void
    {
        $div = new Tag('div', 'testId');
        Dom::addElement($div);
        $this->assertEquals(Dom::getById('testXid'), null);
    }

    public function testAddHeader()
    {
        $script = '<script src="/test/script.js"></script>';
        Dom::addHeader($script);
        $this->assertEquals(Dom::headerExists($script), true);
    }

    public function testGetHeaders()
    {
        Dom::resetHeaders();
        $script1 = '<script src="/test/script1.js"></script>';
        Dom::addHeader($script1);
        $script2 = '<script src="/test/script2.js"></script>';
        Dom::addHeader($script2);
        $this->assertEquals(Dom::getHeaders(), [$script1, $script2]);
    }

    public function testAddFooter()
    {
        $script = '<script src="/test/script.js"></script>';
        Dom::addFooter($script);
        $this->assertEquals(Dom::footerExists($script), true);
    }

    public function testGetFooters()
    {
        Dom::resetFooters();
        $script1 = '<script src="/test/script1.js"></script>';
        Dom::addFooter($script1);
        $script2 = '<script src="/test/script2.js"></script>';
        Dom::addFooter($script2);
        $this->assertEquals(Dom::getFooters(), [$script1, $script2]);
    }
}
