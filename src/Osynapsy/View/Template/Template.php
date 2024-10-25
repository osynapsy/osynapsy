<?php
namespace Osynapsy\View\Template;

use Osynapsy\Html\Tag;
use Osynapsy\Html\DOM;
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
    protected $title;
    protected $parts = [self::BODY_PART_ID => []];
    protected $template = '<!--main-->';

    public function init()
    {
    }

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
        return $this->template;
    }

    public function getDomComponents() : array
    {
        return DOM::getAllComponents() ?? [];
    }

    public function get()
    {
        return $this->buildFullTemplate();
    }

    protected function buildFullTemplate()
    {
        $template = $this->template;
        $this->add($this->title, 'title');
        foreach (DOM::getRequire() as $require) {
            $this->addComponentRequirement($require[1], $require[2] ?? $require[0]);
        }
        foreach($this->parts as $id => $parts) {
            $template = str_replace(sprintf('<!--%s-->', $id), implode(PHP_EOL, $parts), $template);
        }
        return $template;
    }

    private function addComponentRequirement($type, $url)
    {
        $method = sprintf('add%s', $type);
        $this->{$method}($url);
    }

    public function addCss($cssWebPath)
    {
        $this->addIfNoDuplicate(sprintf('<link id="%s" href="%s" rel="stylesheet" />', sha1($cssWebPath), $cssWebPath), self::CSS_PART_ID);
    }

    public function addStyle($style)
    {
        $this->addIfNoDuplicate(sprintf("<style id=\"%s\">\n%s\n</style>", sha1($style), $style), self::CSS_PART_ID);
    }

    public function addJs($jsWebPath, $scriptId = null)
    {
        $this->addIfNoDuplicate(sprintf('<script id="%s" src="%s"></script>', $scriptId ?? sha1($jsWebPath), $jsWebPath), self::JS_PART_ID);
    }

    public function addScript($code)
    {
        $this->addIfNoDuplicate(sprintf('<script id="%s">%s</script>', sha1($code), $code), self::JS_PART_ID);
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
        $this->parts[$partId][] = (string) $content;
        return $this;
    }

    public function appendLibrary(array $libraries = [], $appendFormController = true)
    {
        $sha1Namespace = sha1('Osynapsy\\');
        if ($appendFormController) {
            $libraries['osynapsyjs'] = sprintf('js/Osynapsy.js?ver=%s', Kernel::VERSION);
            $libraries['osynapsycss'] =  sprintf('css/style.css?ver=%s', Kernel::VERSION);
        }
        foreach ($libraries as $libraryId =>$pathLibrary) {
            if (strpos($pathLibrary, '.css') !== false) {
                $this->addCss(sprintf('/assets/%s/%s', $sha1Namespace, $pathLibrary));
            } else {
                $this->addJs(sprintf('/assets/%s/%s', $sha1Namespace, $pathLibrary), is_string($libraryId) ? $libraryId : null);
            }
        }
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }
}
