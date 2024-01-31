<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Controller;

use Osynapsy\Event\Dispatcher as EventDispatcher;
use Osynapsy\Event\Event;
use Osynapsy\Http\Request;
use Osynapsy\Http\Response\ResponseInterface;
use Osynapsy\Application\ApplicationInterface;
use Osynapsy\Action\ActionInterface;
use Osynapsy\Database\Driver\DboInterface;
use Osynapsy\Observer\SubjectInterface;

/**
 * Abstract Osynapsy controller.
 *
 * This class is the implementation of "C" of MVC pattern by Osynapsy.
 * The default method is indexAction which is recall if not specific action is recall
 * from fronted.
 *
 */
abstract class AbstractController implements ControllerInterface, SubjectInterface
{
    use \Osynapsy\Observer\Subject;

    private $dispatcher;
    private $application;
    private $externalActions = [];
    private $model;
    private $request;

    /**
     * Contructor of controller,
     *
     * @param Request $request
     * @param Application $application
     */
    public function __construct(Request $request, ApplicationInterface $application)
    {
        $this->request = $request;
        $this->application = $application;
        $this->loadObserver();
    }

    public function dispatchLocalEventAction($eventId)
    {
        if ($this->model) {
            $this->model->find();
        }
        //Call indexAction for load component html and theirs listeners
        $result = autowire()->execute($this, 'indexAction');
        if (is_object($result) && method_exists($result, 'init')) {
            $result->init();
        }
        $this->getDispatcher()->dispatch(new Event($eventId, $this));
    }

    /**
     * Return application instance
     *
     * @return \Osynapsy\Mvc\Application
     */
    final public function getApp() : ApplicationInterface
    {
        return $this->application;
    }

    /**
     * Get $key db connection
     *
     * @param int $key
     * @return Db
     */
    public function getDb($key = 0) : ?DboInterface
    {
        return $this->getApp()->getDb($key);
    }

    /**
     * Return DbFactory instance
     *
     * @return \Osynapsy\Db\DbFactory
     */
    final public function getDbFactory() : \Osynapsy\Database\DboFactory
    {
        return $this->getApp()->getDbFactory();
    }

    /**
     * Return dispatcher instance
     *
     * @return EventDispatcher
     */
    public function getDispatcher() : EventDispatcher
    {
        if (empty($this->dispatcher)) {
            $this->dispatcher = new EventDispatcher($this);
        }
        return $this->dispatcher;
    }

    /**
     * Return external action
     *
     * @return mixed
     */
    final public function getExternalAction(string $actionId) : string
    {
        if (class_exists($actionId)) {
            $actionId = sha1($actionId);
        }
        if (!array_key_exists($actionId, $this->externalActions) ){
            throw new \Exception(sprintf("No external action %s exists", $actionId));
        }
        return $this->externalActions[$actionId];
    }

    /**
     * Return current controller response
     *
     * @return \Osynapsy\Http\Response
     */
    public function getResponse() : ResponseInterface
    {
        return $this->getApp()->getResponse();
    }

    /**
     * Return current request
     *
     * @return \Osynapsy\Kernel\Request
     */
    public function getRequest() : Request
    {
        return $this->request;
    }

    /**
     * Return true if  if controller has a valid Model
     *
     * @return boolean
     */
    public function hasDb() : bool
    {
        return !empty($this->getDb());
    }

    /**
     * Check if external action $actionId is presente
     *
     * @param string $actionId
     * @return boolean
     */
    public function hasExternalAction($actionId) : bool
    {
        return array_key_exists($actionId, $this->externalActions);
    }

    /**
     * Set external class action for manage action
     *
     * @param string $actionClass namespace of class to execute
     * @param string $actionId id used to retrive the class
     * @return void
     */
    public function setExternalAction(string $actionClass, string $actionId = null) : void
    {
        if (!in_array(ActionInterface::class, class_implements($actionClass) ?: [])) {
            throw new \Exception(sprintf("Class %s must implement %s", $actionClass, ActionInterface::class));
        }
        if (!method_exists($actionClass, 'execute')) {
            throw new \Exception(sprintf("External action class \"%s\" must implement execute method", $actionClass));
        }
        $this->externalActions[$actionId ?? sha1($actionClass)] = $actionClass;
    }

    /**
     * Open javascript alert on the view
     *
     * @param string $message to show
     *
     */
    public function alert($message)
    {
        $this->js(sprintf("alert(['%s'])", addslashes($message)));
    }

    /**
     * Redirect browser to location in $url parameter indicate
     *
     * @param string $url
     */
    public function go($url)
    {
        $this->response->message('command', 'goto', $url);
    }

    /**
     * Refresh component ids on the view
     *
     * @param array $components
     */
    public function refreshComponents(array $components)
    {
        $this->js(sprintf("Osynapsy.refreshComponents(['%s'])", implode("','", $components)));
    }

    /**
     * Refresh component ids on the parent view
     *
     * @param array $components
     */
    public function refreshParentComponents(array $components)
    {
        $this->js(sprintf("parent.Osynapsy.refreshComponents(['%s'])", implode("','", $components)));
    }

    /**
     * Hide modal $modalId on view
     *
     * @param string $modalId id of the modal to hide
     *
     */
    public function closeModal()
    {
        $this->js(sprintf("parent.$('#%s').modal('hide')", 'amodal'));
    }

    public function historyPushState($id)
    {
        $this->js(sprintf("history.pushState(null,null,'%s');", $id));
    }

    /**
     * Send js code to eval and execute on view
     *
     * @param string $jscode code javascript
     *
     */
    public function js($jscode)
    {
        $this->response->message('command', 'execCode', str_replace(PHP_EOL,'\n', $jscode));
    }
}
