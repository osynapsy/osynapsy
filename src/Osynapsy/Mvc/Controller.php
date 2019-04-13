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

use Osynapsy\Event\Dispatcher;
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
    public $model;
    
    public function __construct(Request $request = null, $application = null)
    {        
        $this->application = $application;
        $this->parameters = $request->get('page.route')->parameters;        
        $this->request = $request;
        $this->dispatcher = new Dispatcher($this);
        $this->loadObserver();
        $this->setState('init');
        $this->init();
        $this->setState('initEnd');
    }
    
    public function deleteAction()
    {
        if ($this->model) {
            $this->model->delete();
        }
    }
    
    private function execAction($cmd)
    {
        $this->setResponse(new JsonResponse());
        //$cmd = $_REQUEST[$this->actionKey];
        //sleep(0.7);
        $this->setState($cmd.'ActionStart');
        if (!method_exists($this, $cmd.'Action')) {
            $res = 'No action '.$cmd.' exist in '.get_class($this);
        } elseif (!empty($_REQUEST['actionParameters'])){
            $res = call_user_func_array(
                array($this, $cmd.'Action'),
                $_REQUEST['actionParameters']
            );
        } else {
            $res = $this->{$cmd.'Action'}();
        }
        $this->setState($cmd.'ActionEnd');
        if (!empty($res) && is_string($res)) {
            $this->response->error('alert',$res);
        }
        return $this->response;
    }

    public function getApp()
    {
        return $this->application;
    }
    
    public function getDb($key = 0)
    {
        return $this->getApp()->getDb($key);
    }
    
    public function getDbFactory()
    {
        return $this->getApp()->getDbFactory();
    }
    
    public function getDispacther()
    {
        return $this->dispatcher;
    }
    
    public function getModel()
    {
        return $this->model;
    }
    
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
    
    public function getResponse()
    {
        return $this->response;
    }
    
    public function getRequest()
    {
        return $this->request;
    }    
    
    public function getState()
    {
        return $this->state;
    }
    
    abstract public function indexAction();
    
    abstract public function init();
    
    public function loadView($path, $params = array(), $return = false)
    {
        $view = $this->getResponse()->getBuffer($path, $this);
        if ($return) {
            return $view;
        }
        $this->response->addContent($view);
    }
    
    public function run()
    {
        $cmd = filter_input(\INPUT_SERVER, 'HTTP_OSYNAPSY_ACTION');
        if (!empty($cmd)) {
            return $this->execAction($cmd);
        }        
        $this->setResponse(new HtmlResponse())->loadTemplate(
            $this->request->get('page.route')->template,
            $this
        );
        if ($this->model) {
            $this->model->find();
        }
        $resp = $this->indexAction();
        if ($resp) {
            $this->response->addContent($resp);
        }
        return $this->response;
    }
    
    public function saveAction()
    {
        if ($this->model) {
            $this->model->save();
        }
    }
    
    public function setResponse(Response $response)
    {
        return $this->response = $response;
    }
}
