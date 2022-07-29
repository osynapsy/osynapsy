<?php
namespace Osynapsy\Html;

use Osynapsy\Mvc\InterfaceController;

class Template
{
    protected $path;
    protected $controller;
    protected $template;
    protected $keys;

    public function __construct($path, InterfaceController $controller)
    {
        $this->path = $path;
        $this->controller = $controller;
        $this->validatePath();
        $this->initTemplate();
        $this->initKeys();
    }

    protected function validatePath()
    {
        if (!is_file($this->path)) {
            throw new \Exception(sprintf('Template file %s not exists', $this->path));
        }
    }

    protected function initTemplate()
    {
        $controller = $this->getController();
        include $this->getPath();
        $this->template = ob_get_contents();
        ob_clean();
    }

    protected function initKeys()
    {
        preg_match_all('/<\!--(.*?)-->/', $this->template, $this->keys);
    }

    protected function getController()
    {
        return $this->controller;
    }

    protected function getPath()
    {
        return $this->path;
    }

    public function get(array $contents)
    {
        $html = $this->template;
        $layoutKeys = array_column($this->keys, 0);
        foreach($contents as $contentKey => $content) {
            $result = array_search($contentKey, $layoutKeys);
            if ($result !== false) {
                $html = str_replace($this->keys[$result][1], $content, $html);
            }
        }
        return $html;
    }
}
