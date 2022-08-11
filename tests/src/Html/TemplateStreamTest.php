<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Osynapsy\Html\Template;
use Osynapsy\Html\Ocl\DataGrid;

final class TemplateTest extends TestCase
{
    const VALID_TEMPLATE_PATH = 'templatevalid.html';
    const INVALID_TEMPLATE_PATH = 'templateinvalid.html';

    protected static $template;

    public static function setUpBeforeClass() : void
    {
        self::$template = new Template();
    }

    public static function getFullPath($path)
    {
        return __DIR__ . '/' . $path;
    }

    public static function template($path)
    {
        self::$template->setPath(self::getFullPath($path));
        return self::$template;
    }

    public function testTemplatePathIsValid()
    {
        $template = $this->template(self::VALID_TEMPLATE_PATH);
        $this->assertNotEmpty($template->getPath());
        $this->assertEquals($this->getFullPath(self::VALID_TEMPLATE_PATH), $template->getPath());
    }

    public function testTemplatePathIsNotValid()
    {
        $this->expectException("Exception");
        $this->template(self::INVALID_TEMPLATE_PATH);
    }

    public function testTemplateContentRaw()
    {
        $templateContent = file_get_contents($this->getFullPath(self::VALID_TEMPLATE_PATH));
        $template = $this->template(self::VALID_TEMPLATE_PATH);
        $this->assertEquals($templateContent, $template->getRaw());
    }

    public function testTemplateAddJs()
    {
        $scriptPath = '/test/script.js';
        $regexPattern = sprintf('/<script src="\/test\/script.js"><\/script>/', $scriptPath);
        $template = $this->template(self::VALID_TEMPLATE_PATH);
        $template->addJs($scriptPath);
        $this->assertMatchesRegularExpression($regexPattern, $template->get());
    }

    public function testTemplateAddJsLocalCode()
    {
        $jsCode = 'function() { let i = 1;}';
        $regexPattern = '/function\(\) \{ let i = 1\;\}/';
        $template = $this->template(self::VALID_TEMPLATE_PATH);
        $template->addJsCode($jsCode);
        $this->assertMatchesRegularExpression($regexPattern, $template->get());
    }

    public function testTemplateAddCss()
    {
        $cssPath = '/test/style.css';
        $regexPattern = sprintf('/<link href="\/test\/style.css" rel="stylesheet" \/>/', $cssPath);
        $template = $this->template(self::VALID_TEMPLATE_PATH);
        $template->addCss($cssPath);
        $this->assertMatchesRegularExpression($regexPattern, $template->get());
    }

    public function testTemplateAddStyle()
    {
        $style = '.text {font-size: 10px}';
        $regexPattern = '/.text \{font-size\: 10px\}/';
        $template = $this->template(self::VALID_TEMPLATE_PATH);
        $template->addStyle($style);
        $this->assertMatchesRegularExpression($regexPattern, $template->get());
    }

    public function testTemplateComponentIncludeComponent()
    {
        $DataGrid = new DataGrid('testGrid');
        $template = $this->template(self::VALID_TEMPLATE_PATH);
        $template->add($DataGrid);
        $result = $template->get(true);
        //Test script include
        $this->assertMatchesRegularExpression('/DataGrid/', $result);
        $this->assertMatchesRegularExpression('/id="testGrid"/', $result);
    }

    public function testTemplateReturnOnlyComponent()
    {
        $_SERVER['HTTP_OSYNAPSY_HTML_COMPONENTS'] = 'testGrid';
        $DataGrid = new DataGrid('testGrid');
        $DataGrid->setData([['prova' => '1']]);
        $regexPattern = '/testGrid/';
        $template = $this->template(self::VALID_TEMPLATE_PATH);
        $this->assertMatchesRegularExpression($regexPattern, $template->get());
    }
}

