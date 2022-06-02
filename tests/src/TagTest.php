<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Osynapsy\Html\Tag;

final class TagTest extends TestCase
{
    public function testTag(): void
    {
        $Tag = new Tag('span');
        $this->assertEquals(trim($Tag->get()), '<span></span>');
    }

    public function testId()
    {
        $Tag = new Tag('span', 'xyz');
        $this->assertEquals(trim($Tag->get()), '<span id="xyz"></span>');
    }

    public function testClass()
    {
        $Tag = new Tag('span', null, 'abc');
	$this->assertEquals(trim($Tag->get()), '<span class="abc"></span>');
    }

    public function testIdClass()
    {
        $Tag = new Tag('span', 'xyz', 'abc');
        $this->assertEquals(trim($Tag->get()), '<span id="xyz" class="abc"></span>');
    }

    public function testParent()
    {
        $Tag = new Tag('span', 'xyz', 'abc');
        $Parent = new Tag('div');
        $Parent->add($Tag);
        $this->assertEquals($Parent, $Tag->parent);
    }
}

