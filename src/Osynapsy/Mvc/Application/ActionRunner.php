<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Osynapsy\Mvc\Application;

use Osynapsy\Mvc\Controller\ControllerInterface;
use Osynapsy\Http\Response\ResponseInterface;

/**
 * Description of ActionRunner
 *
 * @author Pietro Celeste <pietro.celeste@gmail.com>
 */
class ActionRunner
{
    protected $controller;

    public function __construct(ControllerInterface $controller)
    {
        $this->controller = $controller;
    }

    public function getController()
    {
        return $this->controller;
    }

    /**
     * Run controller and execute request action
     *
     * @param string $action
     * @param array $parameters
     * @return \Osynapsy\Http\Response
     */
    public function run($actionId, $parameters = [])
    {
        if (empty($actionId)) {
            return $this->execDefaultAction();
        }
        if ($this->getController()->hasExternalAction($actionId)) {
            return $this->execExternalAction($actionId, $parameters);
        }
        if (method_exists($this->getController(), $actionId.'Action')) {
            return $this->execInternalAction($actionId, $parameters);
        }
        return $this->getResponse()->alertJs(sprintf('No action %s exist in %s', $actionId, get_class($this->controller)));
    }

    /**
     * Recall index action (default action)
     *
     * @return \Osynapsy\Http\Response
     */
    private function execDefaultAction() : ResponseInterface
    {
        if ($this->getController()->hasModel()) {
            $this->getController()->getModel()->find();
        }
        $response = $this->getController()->indexAction();
        if ($response) {
            $this->getController()->getTemplate()->add($response);
        }
        $this->getResponse()->addContent($this->getController()->getTemplate()->get());
        return $this->getResponse();
    }

    /**
     * Recall and execute an external action class
     *
     * @param string $action
     * @param array $parameters
     * @return \Osynapsy\Http\Response
     */
    public function execExternalAction(string $actionId, array $parameters = []) : ResponseInterface
    {
        $actionHandle = $this->getController()->getExternalActions($actionId);
        $actionHandle->setController($this->getController());
        $actionHandle->setParameters($parameters);
        $message = $actionHandle->execute();
        if (!empty($message)) {
            $this->getResponse()->alertJs($message);
        }
        return $this->getResponse();
    }

    /**
     * Recall internal method action of controller
     *
     * @param string $action
     * @param array $parameters
     * @return \Osynapsy\Http\Response
     */
    private function execInternalAction(string $action, array $parameters) : ResponseInterface
    {
        $response = !empty($parameters)
                  ? call_user_func_array( [$this, $action.'Action'], $parameters)
                  : $this->{$action.'Action'}();
        if (!empty($response) && is_string($response)) {
            $this->getResponse()->alertJs($response);
        }
        return $this->getResponse();
    }

    protected function getResponse() : ResponseInterface
    {
        return $this->getController()->getResponse();
    }
}
