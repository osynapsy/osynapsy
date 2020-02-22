<?php
namespace Osynapsy\Mvc\Action;

use Osynapsy\Mvc\Controller;

/**
 * Description of Base
 *
 * @author Pietro
 */
abstract class Base implements InterfaceAction
{
    protected $controller;
    protected $parameters;
    
    abstract public function execute();
    
    public function getController()
    {
        return $this->controller;
    }
    
    public function getModel()
    {
        return $this->getController()->getModel();
    }
    
    public function getResponse()
    {
        return $this->getController()->getResponse();
    }
    
    public function setController(Controller $controller)
    {
        $this->controller = $controller;
    }
    
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }
}
