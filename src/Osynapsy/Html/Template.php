<?php
namespace Osynapsy\Html;

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;

/**
 * Manage html template
 *
 * @author Pietro Celeste <p.celeste@opensymap.net>
 */
class Template
{
    const JS_PART_ID = 'js';
    const CSS_PART_ID = 'css';
    const BODY_PART_ID = 'body';

    protected $path;
    protected $parts = [];

    /**
     * Unused method (for future develop)
     */

    private function initKeys()
    {
        preg_match_all('/<\!--(.*?)-->/', $this->template, $this->keys);
    }

    public function setPath($path)
    {
        $this->path = $path;
        $this->validatePath($path);
    }

    public function getPath()
    {
        return $this->path;
    }

    public function validatePath($path)
    {
        if (!is_file($path)) {
            throw new \Exception('File not exists', 404);
        }
    }

    public function getRaw()
    {
        include $this->getPath();
        $template = ob_get_contents();
        ob_clean();
        return $template;
    }

    public function get()
    {
        $componentIDs = empty($_SERVER['HTTP_OSYNAPSY_HTML_COMPONENTS']) ? [] : explode(';', $_SERVER['HTTP_OSYNAPSY_HTML_COMPONENTS']);
        return empty($componentIDs) ? $this->buildFullTemplate() : $this->buildRequestedComponents($componentIDs);
    }

    protected function buildFullTemplate()
    {
        $template = $this->getRaw();
        $this->addComponentRequirements(Component::getRequire());
        foreach($this->parts as $id => $parts) {
            $content = implode(PHP_EOL, $parts);
            $template = str_replace(sprintf('<!--%s-->', $id), $content, $template);
        }
        return $template;
    }

    protected function buildRequestedComponents($componentIDs)
    {
        $response = new Tag('div','response');
        foreach($componentIDs as $componentID) {
            $response->add(Component::getById($componentID));
        }
        return $response->__toString();
    }

    protected function addComponentRequirements($requirements)
    {
        if (!empty($requirements)) {
            foreach ($requirements as $type => $urls) {
                $this->addComponentRequirement($type, $urls);
            }
        }
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

    public function addJs($jsWebPath)
    {
        $this->addIfNoDuplicate(sprintf('<script src="%s"></script>', $jsWebPath), self::JS_PART_ID);
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
}
