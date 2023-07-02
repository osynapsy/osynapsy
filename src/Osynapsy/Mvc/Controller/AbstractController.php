<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc\Controller;

use Osynapsy\Event\Dispatcher as EventDispatcher;
use Osynapsy\Event\Event;
use Osynapsy\Http\Request;
use Osynapsy\Http\Response\ResponseInterface;
use Osynapsy\Mvc\Template\Template;
use Osynapsy\Mvc\Application\ApplicationInterface;
use Osynapsy\Mvc\Action\ActionInterface;
use Osynapsy\Mvc\Model\ModelInterface;
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
    public function __construct(Request $request = null, ApplicationInterface $application = null)
    {
        $this->application = $application;
        $this->parameters = $request->get('page.route')->parameters;
        $this->loadObserver();
        $this->init();
        $this->initTemplate();
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
    public function getDb($key = 0) : DboInterface
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
    final public function getModel() : ModelInterface
    {
        return $this->model ?? new class ($this) extends \Osynapsy\Mvc\Model\Simple {};
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
    public function getResponse() : ResponseInterface
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

    public function hasModel()
    {
        return !empty($this->model);
    }

    public function hasExternalAction($actionId)
    {
        return array_key_exists($actionId, $this->externalActions);
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
    public function setExternalAction(string $actionId, ActionInterface $actionClass) : void
    {
        $this->externalActions[$actionId] = $actionClass;
    }

    /**
     * Set model for controller
     *
     * @param InterfaceModel $model
     */
    public function setModel(ModelInterface $model)
    {
        $this->model = $model;
    }

    /**
     * Set response for current controller
     *
     * @param Response $response
     * @return Response
     */
    public function setResponse(ResponseInterface $response) : ResponseInterface
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
