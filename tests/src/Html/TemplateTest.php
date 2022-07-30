<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Osynapsy\Html\Template2;

final class TemplateTest extends TestCase
{
    const VALID_TEMPLATE_PATH = 'templatevalid.html';
    const INVALID_TEMPLATE_PATH = 'templateinvalid.html';
    
    public function testTemplateExists(): void
    {        
        $Template = new Template2(__DIR__.'/'.self::VALID_TEMPLATE_PATH);
        $this->assertNotEmpty($Template);        
    }
    
    public function testTemplatePathIsValid()
    {
        $path = __DIR__.'/'.self::VALID_TEMPLATE_PATH;
        $Template = new Template2($path);
        $this->assertNotEmpty($Template->getPath());
        $this->assertEquals($path, $Template->getPath());
    }
    
    public function testTemplatePathIsNotValid()
    {
        $this->expectException("Exception");
        $path = __DIR__.'/'.self::INVALID_TEMPLATE_PATH;
        $Template = new Template2($path);        
    }
}

