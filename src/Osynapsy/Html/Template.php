<?php
namespace Osynapsy\Html;

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;
use Osynapsy\Kernel;

/**
 * Description of Template
 *
 * @author Pietro Celeste <p.celeste@opensymap.net>
 */
class Template
{
    const JS_PART_ID = 'js';
    const CSS_PART_ID = 'css';
    const BODY_PART_ID = 'main';

    protected $controller;
    protected $path;
    protected $parts = [];
    protected $template = '<!--main-->';

    private function initKeys()
    {
        preg_match_all('/<\!--(.*?)-->/', $this->template, $this->keys);
    }

    public function setPath($path)
    {
        $this->path = $path;
        $this->template = $this->include($this->path);
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
        return $this->template;
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
        $template = $this->template;
        foreach (Component::getRequire() as $type => $urls) {
            $this->addComponentRequirement($type, $urls);
        }
        foreach($this->parts as $id => $parts) {
            $template = str_replace(sprintf('<!--%s-->', $id), implode(PHP_EOL, $parts), $template);
        }
        return $template;
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
        if (!array_key_exists($partId, $this->parts) || !in_array($content, $this->parts[$partId])) {
            $this->parts[$partId][] = $content;
        }
    }

    public function add($content, $partId = self::BODY_PART_ID)
    {
        if (!array_key_exists($partId, $this->parts)) {
            $this->parts[$partId] = [];
        }
        $this->parts[$partId][] = $content;
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
