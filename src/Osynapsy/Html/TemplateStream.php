<?php
namespace Osynapsy\Html;

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;
use Osynapsy\Http\Psr7\Message\Stream\StreamString;
use Osynapsy\Kernel;

/**
 * Description of Template
 *
 * @author Pietro Celeste <p.celeste@opensymap.net>
 */
class TemplateStream
{
    const JS_PART_ID = 'js';
    const CSS_PART_ID = 'css';
    const BODY_PART_ID = 'main';

    protected $controller;
    protected $path;
    protected $parts = [];
    protected $template = '<!--main-->';
    protected $stream;

    public function __construct()
    {
        $this->stream = new StreamString($this->template);
    }

    public function init()
    {
    }

    public function getStream()
    {
        return $this->stream;
    }

    private function initKeys()
    {
        preg_match_all('/<\!--(.*?)-->/', $this->template, $this->keys);
    }

    public function setPath($path)
    {
        $this->path = $path;
        $this->getStream()->write($this->include($this->path));
    }

    public function include($path)
    {
        if (!is_file($path)) {
            throw new \Exception(sprintf('Template %s not exists', $path), 404);
        }
        include $path;
        $template = ob_get_contents();
        ob_clean();
        return $template;
    }

    public function setController($controller)
    {
        $this->controller = $controller;
        $this->init();
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getRaw()
    {
        $this->getStream()->rewind();
        return $this->getStream()->getContents();
    }

    public function get()
    {
        $componentIDs = empty($_SERVER['HTTP_OSYNAPSY_HTML_COMPONENTS']) ? [] : explode(';', $_SERVER['HTTP_OSYNAPSY_HTML_COMPONENTS']);
        return !empty($componentIDs) ? $this->buildRequestedComponents($componentIDs) : $this->buildFullTemplate();
    }

    protected function buildRequestedComponents($componentIDs)
    {
        $response = new Tag('div','response');
        foreach($componentIDs as $componentID) {
            $response->add(Component::getById($componentID));
        }
        return $response->get();
    }

    protected function buildFullTemplate()
    {
        foreach (Component::getRequire() as $type => $urls) {
            $this->addComponentRequirement($type, $urls);
        }
        $this->getStream()->rewind();
        return $this->getStream()->getContents();
    }

    private function addComponentRequirement($type, $urls)
    {
        foreach ($urls as $url) {
            $method = sprintf('add%s', $type);
            $this->{$method}($url);
        }
    }

    public function addCss($cssWebPath)
    {
        $this->addIfNoDuplicate(sprintf('<link href="%s" rel="stylesheet" />', $cssWebPath), self::CSS_PART_ID);
    }

    public function addStyle($style)
    {
        $this->addIfNoDuplicate('<style>'.PHP_EOL.$style.PHP_EOL.'</style>', self::CSS_PART_ID);
    }

    public function addJs($jsWebPath, $scriptId = '')
    {
        $this->addIfNoDuplicate(sprintf('<script src="%s"%s></script>', $jsWebPath, empty($scriptId) ? '' : " id=\"$scriptId\""), self::JS_PART_ID);
    }

    public function addJsCode($code)
    {
        $this->addIfNoDuplicate('<script>'.PHP_EOL.$code.PHP_EOL.'</script>', self::JS_PART_ID);
    }

    public function addIfNoDuplicate($content, $partId = self::BODY_PART_ID)
    {
        if ($this->getStream()->search($content, 0) === false) {
            $this->add($content, $partId);
        }
    }

    public function add($content, $partId = self::BODY_PART_ID)
    {
        $position = $this->getStream()->search(sprintf('<!--%s-->', $partId), 0);
        if ($position !== false) {
            $this->getStream()->prepend((string) $content, $partId);
        }
    }

    public function appendLibrary(array $optionalLibrary = [], $appendFormController = true)
    {
        foreach ($optionalLibrary as $pathLibrary) {
            if (strpos($pathLibrary, '.css') !== false) {
                $this->addCss('/assets/osynapsy/'.Kernel::VERSION.$pathLibrary);
                continue;
            }
            $this->addJs('/assets/osynapsy/'.Kernel::VERSION.$pathLibrary);
        }
        if (!$appendFormController) {
            return;
        }
        $this->addJs('/assets/osynapsy/'.Kernel::VERSION.'/js/Osynapsy.js', 'osynapsyjs');
        $this->addCss('/assets/osynapsy/'.Kernel::VERSION.'/css/style.css');
    }
}
