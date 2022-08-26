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
use Osynapsy\Mvc\InterfaceController;

abstract class BaseView implements InterfaceView
{
    protected $components = array();
    private $controller;

    public function __construct(InterfaceController $controller, $title = '')
    {
        $this->controller = $controller;
        $this->setTitle($title);
    }

    protected function add($part)
    {
        $this->getTemplate()->add($part);
        if (is_object($part)) {
            return $part;
        }
    }

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

    public function addJsCode($code)
    {
        $this->getTemplate()->addJsCode($code);
    }

    public function addJsLibrary($path)
    {
        $this->addJs(sprintf('/assets/osynapsy/%s/%s', Kernel::VERSION, $path));
    }

    public function addMeta($property ,$content)
    {
        $meta = new \Osynapsy\Html\Tag('meta');
        $meta->att(['property' => $property, 'content' => $content]);
        $this->getTemplate()->add($meta, 'meta');
    }

    public function addStyle($style)
    {
        $this->getTemplate()->addStyle($style);
    }

    public function get()
    {
        return $this->init();
    }

    public function getController()
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
        return $this->get().'';
    }

    abstract public function init();
}
