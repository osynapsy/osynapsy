<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc;

use Osynapsy\Event\Dispatcher as EventDispatcher;
use Osynapsy\Mvc\Application;
use Osynapsy\Http\Request;
use Osynapsy\Http\Response;
use Osynapsy\Http\ResponseJson as JsonResponse;
use Osynapsy\Http\ResponseHtmlOcl as HtmlResponse;
use Osynapsy\Observer\InterfaceSubject;

abstract class Controller implements InterfaceController, InterfaceSubject
{
    use \Osynapsy\Observer\Subject;
    
    private $parameters;
    private $dispatcher;
    private $request;
    private $response;
    private $application;
    private $externalActions = [];
    public $model;
    
    /**
     * Contructor of controller,
     * 
     * @param Request $request
     * @param Application $application
     */
    public function __construct(Request $request = null, Application $application = null)
    {        
        $this->application = $application;
        $this->parameters = $request->get('page.route')->parameters;        
        $this->request = $request;
        $this->loadObserver();
        $this->setState('beforeInit');
        $this->init();
        $this->setState('afterInit');
    }
    
    /**
     * Add an external action class
     * 
     * @param string $id
     * @param string $class
     */
    public function actionAdd(string $id, string $class)
    {
        $this->actions[$id] = $class;
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
    private function execExternalAction(string $action, array $parameters = []) : Response
    {
        $this->setState('beforeAction'.ucfirst($action));
        $actionClass = new \ReflectionClass($this->externalActions[$action]);
        $actionInstance = $actionClass->newInstance($actionClass, $parameters);
        $actionInstance->setController($this);
        $this->getResponse()->alertJs($actionInstance->run());
        $this->setState('afterAction'.ucfirst($action));
        return $this->getResponse();
    }
    
    /**
     * Recall index action (default action)
     * 
     * @return \Osynapsy\Http\Response
     */
    private function execIndexAction() : Response
    {
        $this->setResponse(new HtmlResponse())->loadTemplate(
            $this->getRequest()->get('page.route')->template,
            $this
        );
        if ($this->model) {
            $this->model->find();
        }
        $response = $this->indexAction();
        if ($response) {
            $this->getResponse()->addContent($response);
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
    final public function getApp() : \Osynapsy\Mvc\Application
    {
        return $this->application;
    }
    
    /**
     * Get $key db connection
     * 
     * @param int $key
     * @return Db
     */
    public function getDb($key = 0) : \Osynapsy\Db\Driver\InterfaceDbo
    {
        return $this->getApp()->getDb($key);
    }
    
    /**
     * Return DbFactory instance
     * 
     * @return \Osynapsy\Mvc\Application
     */
    final public function getDbFactory()
    {
        return $this->getApp()->getDbFactory();
    }
    
    /**
     * Return dispatcher instance
     * 
     * @return \Osynapsy\Mvc\Application
     */
    public function getDispatcher() : EventDispatcher
    {
        if (empty($this->dispatcher)) {
            $this->dispatcher = new EventDispatcher($this);
        }
        return $this->dispatcher;
    }
    
     /**
     * Return model instance
     * 
     * @return \Osynapsy\Mvc\Application
     */
    final public function getModel()
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
        return $this->response;
    }
    
    /**
     * Return current request
     * 
     * @return \Osynapsy\Kernel\Request
     */
    public function getRequest($key = null)
    {
        return is_null($key) ? $this->request : $this->request->get($key); 
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
    public function loadView(string $path, array $params = [], bool $return = false)
    {
        $view = $this->getResponse()->getBuffer($path, $this);
        if ($return) {
            return $view;
        }
        $this->getResponse()->addContent($view);
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
        $this->setResponse(new JsonResponse());
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
    
    /**
     * Set response for current controller
     * 
     * @param Response $response
     * @return Response
     */
    public function setResponse(Response $response) : Response
    {
        return $this->response = $response;
    }
}
