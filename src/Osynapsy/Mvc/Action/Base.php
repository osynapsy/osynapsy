<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc\Action;

use Osynapsy\Mvc\Controller;
use Osynapsy\Db\Driver\InterfaceDbo;
use Osynapsy\Mvc\InterfaceApplication;
use Osynapsy\Mvc\InterfaceController;
use Osynapsy\Mvc\InterfaceModel;

/**
 * Base class for implement an external action.
 * External action is a class which implement all code to respond
 * frontend action event.
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
abstract class Base implements InterfaceAction
{
    private $controller;
    private $parameters = [];
    protected $triggers = [];

    /**
     * Main method which controller recall when Frontend action is recalled.
     */
    abstract public function execute();

    protected function executeTrigger($eventId)
    {
        if (empty($this->triggers[$eventId])) {
            return;
        }
        call_user_func($this->triggers[$eventId], $this);
    }

    /**
     * Wrapper of getApp controller method
     *
     * @return InstanceApplication
     */
    public final function getApp()
    {
        return $this->getController()->getApp();
    }

    /**
     * Get current controller instance.
     *
     * @return Controller
     */
    public final function getController() : InterfaceController
    {
        return $this->controller;
    }

    /**
     * Get current database connection
     *
     * @return InterfaceDbo
     */
    public function getDb() : InterfaceDbo
    {
        return $this->controller->getDb();
    }

    /**
     * Get the current model
     *
     * @return Model
     */
    public function getModel() : InterfaceModel
    {
        return $this->getController()->getModel();
    }

    /**
     * Get the n paramenter from the frontedn request
     *
     * @param int $index
     * @return mixed
     */
    public final function getParameter($index)
    {
        return array_key_exists($index, $this->parameters) ? $this->parameters[$index] : null;
    }

    /**
     * Get the current response
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->getController()->getResponse();
    }

    /**
     * Set controller
     *
     * @param Controller $controller
     */
    public function setController(Controller $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Set action parameters from frontend
     *
     * @param array $parameters
     */
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

    /**
     * Raise an exception
     *
     * @param string $message Exception message
     * @param int $id
     * @throws \Exception
     */
    protected function raiseException($message, $id = 100)
    {
        throw new \Exception($message, $id);
    }
}
