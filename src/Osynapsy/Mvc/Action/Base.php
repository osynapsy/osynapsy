<?php
namespace Osynapsy\Mvc\Action;

use Osynapsy\Mvc\Controller;
use Osynapsy\Db\Driver\InterfaceDbo;

/**
 * Description of Base
 *
 * @author Pietro
 */
abstract class Base implements InterfaceAction
{
    private $controller;
    private $parameters = [];
    protected $triggers = [];

    abstract public function execute();

    protected function executeTrigger($eventId)
    {
        if (empty($this->triggers[$eventId])) {
            return;
        }
        call_user_func($this->triggers[$eventId], $this);
    }

    public final function getApp()
    {
        return $this->controller->getApp();
    }

    public final function getController()
    {
        return $this->controller;
    }

    public function getDb() : InterfaceDbo
    {
        return $this->controller->getDb();
    }

    public function getModel()
    {
        return $this->getController()->getModel();
    }

    public final function getParameter($index)
    {
        return array_key_exists($index, $this->parameters) ? $this->parameters[$index] : null;
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

    protected function raiseException($message, $id = 100)
    {
        throw new \Exception($message, $id);
    }
}
