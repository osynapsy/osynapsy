<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc\View;

use Osynapsy\Kernel;
use Osynapsy\Mvc\Controller\ControllerInterface;
use Osynapsy\Html\DOM;
use Osynapsy\Html\Tag;

abstract class AbstractView implements ViewInterface
{
    private $controller;

    public function __construct(ControllerInterface $controller)
    {
        $this->controller = $controller;
    }

    abstract public function init();

    public function addCss($path)
    {
        $this->getTemplate()->addCss($path);
    }

    public function addCssLibrary($path)
    {
        $this->addCss(sprintf('/assets/osynapsy/%s/%s', Kernel::VERSION, $path));
    }

    public function addJs($path)
    {
        $this->getTemplate()->addJs($path);
    }

    public function addScript($code)
    {
        $this->getTemplate()->addScript($code);
    }

    public function addJsLibrary($path)
    {
        $this->addJs(sprintf('/assets/osynapsy/%s/%s', Kernel::VERSION, $path));
    }

    public function addMeta($property ,$content)
    {
        $meta = new Tag('meta');
        $meta->attributes(['property' => $property, 'content' => $content]);
        $this->getTemplate()->add($meta, 'meta');
    }

    public function addStyle($style)
    {
        $this->getTemplate()->addStyle($style);
    }

    public function getController() : ControllerInterface
    {
        return $this->controller;
    }

    public function getModel()
    {
        return $this->getController()->getModel();
    }

    public function getTemplate()
    {
        return $this->getController()->getTemplate();
    }

    public function getDb()
    {
        return $this->getController()->getDb();
    }

    public function setTitle($title)
    {
        $this->getTemplate()->add($title, 'title');
    }

    public function __toString()
    {
        $requestComponentIDs = empty($_SERVER['HTTP_OSYNAPSY_HTML_COMPONENTS']) ? [] : explode(';', $_SERVER['HTTP_OSYNAPSY_HTML_COMPONENTS']);
        $view = $this->init();
        if (!empty($this->getModel())) {
            $this->setComponentValues(DOM::getAllComponents());
        }
        return (string) empty($requestComponentIDs) ? $view : $this->refreshComponentsViewFactory($requestComponentIDs);
    }

    protected function setComponentValues($components)
    {
        array_walk($components, function ($component) {
            if (method_exists($component, 'setValue')) {
                $componentId = $component->getAttribute('id');
                $component->setValue($_REQUEST[$componentId] ?? $this->getModel()->getFieldValue($componentId));
            }
        });
    }

    protected function refreshComponentsViewFactory($componentIDs)
    {
        $response = new Tag('div', 'response');
        foreach($componentIDs as $componentId) {
            $response->add(DOM::getById($componentId));
        }
        return $response;
    }
}
