<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Ocl;

use Osynapsy\Mvc\Controller;
use Osynapsy\Html\Tag;

abstract class BaseView
{
    protected $components = array();
    private $controller;       
    private $dummy;
    
    public function __construct(Controller $controller, $title = '')
    {
        $this->controller = $controller;        
        $this->setTitle($title);
        $this->dummy = new Tag();
    }
    
    protected function add($part)
    {
        $this->dummy->add($part);
    }
    
    public function get()
    {        
        return $this->dummy->add($this->init());        
    }

    public function getController()
    {
        return $this->controller;
    }
    
    public function getModel()
    {
        return $this->getController()->getModel();
    }
    
    public function getDb()
    {
        return $this->getController()->getDb();
    }
    
    public function setTitle($title)
    {
        $this->getController()->getResponse()->addContent($title,'title');
    }
    
    public function addJs($path)
    {    
        $this->getController()->getResponse()->addJs($path);
    }
    
    public function addCss($path)
    {    
        $this->getController()->getResponse()->addCss($path);
    }
    
    public function addJsCode($code)
    {
        $this->getController()->getResponse()->addJsCode($code);
    }
    
    public function addStyle($style)
    {
        $this->getController()->getResponse()->addStyle($style);
    }
    
    public function __toString()
    {
        return $this->get().'';
    }
    
    abstract public function init();
}
