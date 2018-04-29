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

abstract class BaseView
{
    protected $components = array();
    protected $controller;
    protected $reponse;
    protected $db;    
    
    public function __construct(Controller $controller, $title = null)
    {
        $this->controller = $controller;
        $this->request = $controller->request;
        $this->db = $controller->getDb();        
        if ($title) {
            $this->setTitle($title);
        }
    }
    
    protected function add($part)
    {
        $this->controller->response->send($part);
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
        return $this->getController()->model;
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
