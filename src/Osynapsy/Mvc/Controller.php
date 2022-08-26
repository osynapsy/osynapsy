<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc;

use Osynapsy\Event\Dispatcher as EventDispatcher;
use Osynapsy\Event\Event;
use Osynapsy\Mvc\Application\InterfaceApplication;
use Osynapsy\Http\Request;
use Osynapsy\Http\Response\Base as Response;
use Osynapsy\Mvc\Template\Template;
use Osynapsy\Observer\InterfaceSubject;
use Osynapsy\Mvc\Action\InterfaceAction;

/**
 * Abstract Osynapsy controller.
 *
 * This class is the implementation of "C" of MVC pattern by Osynapsy.
 * The default method is indexAction which is recall if not specific action is recall
 * from fronted.
 *
 */
abstract class Controller implements InterfaceController, InterfaceSubject
{
    use \Osynapsy\Observer\Subject;

    private $parameters;
    private $dispatcher;
    private $application;
    private $template;
    private $externalActions = [];
    private $model;
    protected $view;

    /**
     * Contructor of controller,
     *
     * @param Request $request
     * @param Application $application
     */
    public function __construct(Request $request = null, InterfaceApplication $application = null)
    {
        $this->application = $application;
        $this->parameters = $request->get('page.route')->parameters;
        $this->loadObserver();
        $this->setState('beforeInit');
        $this->init();
        $this->initTemplate();
        $this->setState('afterInit');
    }

    /**
     * Default deleteAction recall delete method of model if exists
     */
    public function deleteAction()
    {
        if ($this->model) {
            $this->model->delete();
        }
    }

    /**
     * Recall and execute an external action class
     *
     * @param string $action
     * @param array $parameters
     * @return \Osynapsy\Http\Response
     */
    public function execExternalAction(string $action, array $parameters = []) : Response
    {
        $this->setState('beforeAction'.ucfirst($action));
        $actionInstance = $this->externalActions[$action];
        $actionInstance->setController($this);
        $actionInstance->setParameters($parameters);
        $message = $actionInstance->execute();
        if (!empty($message)) {
            $this->getResponse()->alertJs($message);
        }
        $this->setState('afterAction'.ucfirst($action));
        return $this->getResponse();
    }

    public function dispatchLocalEventAction($eventId)
    {
        if ($this->model) {
            $this->model->find();
        }
        //Call indexAction for load component html and theirs listeners
        $this->indexAction();
        $this->getDispatcher()->dispatch(new Event($eventId, $this));
    }

    /**
     * Recall index action (default action)
     *
     * @return \Osynapsy\Http\Response
     */
    private function execIndexAction() : Response
    {
        if ($this->model) {
            $this->model->find();
        }
        $response = $this->indexAction();
        if ($response) {
            $this->getTemplate()->add($response);
        }
        $this->getResponse()->addContent($this->getTemplate()->get());
        return $this->getResponse();
    }

    /**
     * Load html file template
     *
     * @param string $templateId of template
     * @return void
     */
    private function initTemplate()
    {
        $templateId = $this->getRequest()->getRoute()->template;
        $template = $this->getRequest()->getTemplate($templateId);
        $this->template = empty($template['@value']) ? new Template() : new $template['@value'];
        $this->template->setController($this);
        if (!empty($template) && !empty($template['path'])) {
            $this->template->setPath($template['path']);
        }
    }

    /**
     * Recall internal method action of controller
     *
     * @param string $action
     * @param array $parameters
     * @return \Osynapsy\Http\Response
     */
    private function execInternalAction(string $action, array $parameters) : Response
    {
        $this->setState('beforeAction'.ucfirst($action));
        $response = !empty($parameters)
                  ? call_user_func_array( [$this, $action.'Action'], $parameters)
                  : $this->{$action.'Action'}();
        $this->setState('afterAction'.ucfirst($action));
        if (!empty($response) && is_string($response)) {
            $this->getResponse()->alertJs($response);
        }
        return $this->getResponse();
    }

    /**
     * Return application instance
     *
     * @return \Osynapsy\Mvc\Application
     */
    final public function getApp() : InterfaceApplication
    {
        return $this->application;
    }

    /**
     * Get $key db connection
     *
     * @param int $key
     * @return Db
     */
    public function getDb($key = 0) : \Osynapsy\Database\Driver\InterfaceDbo
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
    final public function getExternalAction($actionId)
    {
        if (!array_key_exists($actionId, $this->externalActions) ){
            throw new \Exception(sprintf("No external action %s exists", $actionId));
        }
        return $this->externalActions[$actionId];
    }

     /**
     * Return model instance
     *
     * @return Model
     */
    final public function getModel() : InterfaceModel
    {
        return $this->model;
    }

    /**
     * Return request $key url parameter
     *
     * @param int $key
     * @return string
     */
    public function getParameter($key)
    {
        if (!is_array($this->parameters)) {
            return null;
        }
        if (!array_key_exists($key, $this->parameters)) {
            return null;
        }
        if ($this->parameters[$key] === '') {
            return null;
        }
        return $this->parameters[$key];
    }

    /**
     * Return current controller response
     *
     * @return \Osynapsy\Http\Response
     */
    public function getResponse() : Response
    {
        return $this->getApp()->getResponse();
    }

    /**
     * Return current request
     *
     * @return \Osynapsy\Kernel\Request
     */
    public function getRequest($key = null)
    {
        return $this->getApp()->getRequest($key);
    }

    /**
     * Return current template object
     *
     * @return \Osynapsy\Html\Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Child class must implement default Action indexAction.
     */
    abstract public function indexAction();

    /**
     * Child class must implement init method
     */
    abstract public function init();

    /**
     * Load html file view in current response
     *
     * @param string $path
     * @param array $params
     * @param bool $return
     * @return void
     */
    public function loadView(string $path)
    {
        $view = $this->getTemplate()->include($path);
        $this->getTemplate()->add($view);
    }

    /**
     * Run controller and execute request action
     *
     * @param string $action
     * @param array $parameters
     * @return \Osynapsy\Http\Response
     */
    public function run($action, $parameters = [])
    {
        if (empty($action)) {
            return $this->execIndexAction();
        }
        if (array_key_exists($action, $this->externalActions)) {
            return $this->execExternalAction($action, $parameters);
        }
        if (method_exists($this, $action.'Action')) {
            return $this->execInternalAction($action, $parameters);
        }
        return $this->getResponse()->alertJs('No action '.$action.' exist in '.get_class($this));
    }

    /**
     * Execute default saveAction (recall save class of model if exists)
     */
    public function saveAction()
    {
        if ($this->model) {
            $this->model->save();
        }
    }

    public function uploadAction()
    {
        $this->saveAction();
    }

    /**
     * Set external class action for manage action
     *
     * @param string $actionId
     * @param string $actionClass
     * @return void
     */
    public function setExternalAction(string $actionId, InterfaceAction $actionClass) : void
    {
        $this->externalActions[$actionId] = $actionClass;
    }

    /**
     * Set model for controller
     *
     * @param InterfaceModel $model
     */
    public function setModel(InterfaceModel $model)
    {
        $this->model = $model;
    }

    /**
     * Set response for current controller
     *
     * @param Response $response
     * @return Response
     */
    public function setResponse(Response $response) : Response
    {
        return $response;
    }

    /**
     * Set view for controller
     *
     * @param InterfaceModel $view
     */
    public function setView(InterfaceView $view)
    {
        $this->view = $view;
    }
}
