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
    protected $triggers = [];
    
    abstract public function execute();
    
    protected function executeTrigger($eventId)
    {
        if (empty($this->triggers[$eventId])) {
            return;
        }        
        call_user_func($this->triggers[$eventId], $this);
    }
    
    public function getController()
    {
        return $this->controller;
    }
    
    public function getDb()
    {
        return $this->controller->getDb();
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
    
    public function setTrigger(array $events, callable $function)
    {
        foreach ($events as $event) {
            $this->triggers[$event] = $function;
        }
    }
}
