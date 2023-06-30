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
    protected static $microtime;

    public static function getFullPath($path)
    {
        return __DIR__ . '/' . $path;
    }

    public static function setUpBeforeClass() : void
    {
        self::$microtime = microtime(true);
    }

    public static function template($path)
    {
        $template = new Template();
        $template->setPath(self::getFullPath($path));
        return $template;
    }

    public function testTemplatePathIsValid()
    {
        $template = $this->template(self::VALID_TEMPLATE_PATH);
        $this->assertNotEmpty($template->getPath());
        $this->assertEquals($this->getFullPath(self::VALID_TEMPLATE_PATH), $template->getPath());
        $this->printExecutionTime();
    }

    public function testTemplatePathIsNotValid()
    {
        $this->expectException("Exception");
        $this->template(self::INVALID_TEMPLATE_PATH);
        $this->printExecutionTime();
    }

    public function testTemplateContentRaw()
    {
        $templateContent = file_get_contents($this->getFullPath(self::VALID_TEMPLATE_PATH));
        $template = $this->template(self::VALID_TEMPLATE_PATH);
        $this->assertEquals($templateContent, $template->getRaw());
        $this->printExecutionTime();
    }

    public function testTemplateAddJs()
    {
        $scriptPath = '/test/script.js';
        $regexPattern = sprintf('/<script src="\/test\/script.js"><\/script>/', $scriptPath);
        $template = $this->template(self::VALID_TEMPLATE_PATH);
        $template->addJs($scriptPath);
        $this->assertMatchesRegularExpression($regexPattern, $template->get());
        $this->printExecutionTime();
    }

    public function testTemplateAddJsLocalCode()
    {
        $jsCode = 'function() { let i = 1;}';
        $regexPattern = '/function\(\) \{ let i = 1\;\}/';
        $template = $this->template(self::VALID_TEMPLATE_PATH);
        $template->addJsCode($jsCode);
        $this->assertMatchesRegularExpression($regexPattern, $template->get());
        $this->printExecutionTime();
    }

    public function testTemplateAddCss()
    {
        $cssPath = '/test/style.css';
        $regexPattern = sprintf('/<link href="\/test\/style.css" rel="stylesheet" \/>/', $cssPath);
        $template = $this->template(self::VALID_TEMPLATE_PATH);
        $template->addCss($cssPath);
        $this->assertMatchesRegularExpression($regexPattern, $template->get());
        $this->printExecutionTime();
    }

    public function testTemplateAddStyle()
    {
        $style = '.text {font-size: 10px}';
        $regexPattern = '/.text \{font-size\: 10px\}/';
        $template = $this->template(self::VALID_TEMPLATE_PATH);
        $template->addStyle($style);
        $this->assertMatchesRegularExpression($regexPattern, $template->get());
        $this->printExecutionTime();
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
        $this->printExecutionTime();
    }

    public function testTemplateReturnOnlyComponent()
    {
        $_SERVER['HTTP_OSYNAPSY_HTML_COMPONENTS'] = 'testGrid';
        $DataGrid = new DataGrid('testGrid');
        $DataGrid->setDataset([['prova' => '1']]);
        $regexPattern = '/testGrid/';
        $template = $this->template(self::VALID_TEMPLATE_PATH);
        $this->assertMatchesRegularExpression($regexPattern, $template->get());
        $_SERVER['HTTP_OSYNAPSY_HTML_COMPONENTS'] = '';
        $this->printExecutionTime();
        ob_flush();
    }

    public function printExecutionTime()
    {
        echo sprintf(PHP_EOL.'Tempo impiegato %s', (microtime(true) - self::$microtime));
    }
}
