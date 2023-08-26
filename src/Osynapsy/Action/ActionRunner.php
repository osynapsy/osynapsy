<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Action;

use Osynapsy\Controller\ControllerInterface;
use Osynapsy\Http\Response\ResponseInterface;

/**
 * Execute request action. If no action is requested exec controller indexAction
 *
 * @author Pietro Celeste <pietro.celeste@gmail.com>
 */
class ActionRunner
{
    protected $controller;
    protected $autowire;

    /**
     *
     * @param ControllerInterface $controller
     */
    public function __construct(ControllerInterface $controller)
    {
        $this->controller = $controller;
        $this->autowire = autowire([$controller, $controller->getResponse()]);
    }

    /**
     * Return current controller
     *
     * @return ControllerInterface
     */
    public function getController() : ControllerInterface
    {
        return $this->controller;
    }

    /**
     * Run controller and execute request action
     *
     * @param string $actionId
     * @param array $parameters
     * @return \Osynapsy\Http\Response
     */
    public function run($defaultAction, $actionId, $parameters = [])
    {
        if (method_exists($this->getController(), 'init')) {
            $this->autowire->execute($this->getController(), 'init');
        }
        if (empty($actionId)) {
            return $this->execDefaultAction($defaultAction);
        }
        if ($this->getController()->hasExternalAction($actionId)) {
            return $this->execExternalAction($actionId, $parameters);
        }
        if (method_exists($this->getController(), $actionId.'Action')) {
            return $this->execInternalAction($actionId.'Action', $parameters);
        }
        return $this->getResponse()->alertJs(sprintf('No action %s exist in %s', $actionId, get_class($this->controller)));
    }

    /**
     * Execute default controller action (default action)
     *
     * @return \Osynapsy\Http\ResponseInterface
     */
    private function execDefaultAction($defaultAction) : ResponseInterface
    {
        $response = $this->autowire->execute($this->getController(), $defaultAction);
        $this->getResponse()->add($response);
        return $this->getResponse();
    }

    /**
     * Recall and execute an external action class
     *
     * @param string $actionId
     * @param array $parameters
     * @return \Osynapsy\Http\Response
     */
    public function execExternalAction(string $actionId, array $parameters = []) : ResponseInterface
    {
        $actionClass = $this->getController()->getExternalAction($actionId);
        $actionHandle = new $actionClass;
        $actionHandle->setController($this->getController());
        $actionHandle->setParameters($parameters);
        $message = $this->autowire->execute($actionHandle, 'execute', $parameters ?? []);
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
    private function execInternalAction(string $action, array $parameters = []) : ResponseInterface
    {
        $response = $this->autowire->execute($this->getController(), $action, $parameters);
        if (!empty($response) && is_string($response)) {
            $this->getResponse()->alertJs($response);
        }
        return $this->getResponse();
    }

    /**
     * Return current response
     *
     * @return ResponseInterface
     */
    protected function getResponse() : ResponseInterface
    {
        return $this->getController()->getResponse();
    }
}
