<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc\Application;

use Osynapsy\Mvc\Controller\ControllerInterface;
use Osynapsy\Http\Response\ResponseInterface;
use Osynapsy\Mvc\View\RefreshComponentsView;
use Osynapsy\Mvc\View\AbstractView;

/**
 * Execute request action. If no action is requested exec controller indexAction
 *
 * @author Pietro Celeste <pietro.celeste@gmail.com>
 */
class ActionRunner
{
    protected $controller;

    /**
     *
     * @param ControllerInterface $controller
     */
    public function __construct(ControllerInterface $controller)
    {
        $this->controller = $controller;
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
     * Execute default controller action (default action)
     *
     * @return \Osynapsy\Http\ResponseInterface
     */
    private function execDefaultAction() : ResponseInterface
    {
        $requestComponentIDs = empty($_SERVER['HTTP_OSYNAPSY_HTML_COMPONENTS']) ? [] : explode(';', $_SERVER['HTTP_OSYNAPSY_HTML_COMPONENTS']);
        if ($this->getController()->hasModel()) {
            $this->getController()->getModel()->find();
        }
        $response = $this->getController()->indexAction();
        return empty($requestComponentIDs) ?
            $this->execIndexAction($response) :
            $this->execRefreshComponentsAction($response, $requestComponentIDs);
    }

    /**
     * Execute default method of controller
     *
     * @param mixed $response
     * @return \Osynapsy\Http\ResponseInterface
     */
    protected function execIndexAction($response) : ResponseInterface
    {
        $this->getController()->getTemplate()->add($response);
        $this->getResponse()->addContent($this->getController()->getTemplate()->get());
        return $this->getResponse();
    }

    protected function execRefreshComponentsAction($masterView, $requestComponentIDs)
    {
        if ($masterView instanceof AbstractView) {
            $masterView->init();
        }
        $refreshView = new RefreshComponentsView($this->getController(), $requestComponentIDs);
        $this->getResponse()->addContent($refreshView);
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
        $actionHandle = $this->getController()->getExternalAction($actionId);
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
