<?php
namespace Osynapsy\Html\Ocl;

use Osynapsy\Core\Controller\Controller;

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
        $this->init();
        return;        
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
        $this->controller->response->addContent($title,'title');
    }
    
    public function addJs($path)
    {    
        $this->controller->response->addJs($path);
    }
    
    public function addCss($path)
    {    
        $this->controller->response->addCss($path);
    }
    
    public function addJsCode($code)
    {
        $this->controller->response->addJsCode($code);
    }
    
    public function __toString()
    {
        return $this->get();
    }
    
    abstract public function init();
}
