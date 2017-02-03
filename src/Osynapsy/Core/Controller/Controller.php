<?php
namespace Osynapsy\Core\Controller;

use Osynapsy\Core\Network\Request;
use Osynapsy\Ocl\Response\Html as HtmlResponse;
use Osynapsy\Core\Response\Response;
use Osynapsy\Core\Response\JsonResponse;

abstract class Controller implements InterfaceController
{
    protected $actionKey = 'k-cmd';
    protected $db;
    private $parameters;
    private $templateId;
    public $model;
    public $request;
    public $response;
    public $app;
        
    public function __construct(Request $request = null, $db = null, $appController = null)
    {
        $this->templateId = $request->get('page.templateId');
        $this->parameters = $request->get('page.parameters');        
        $this->request = $request;
        $this->setDbHandler($db);
        $this->app = $appController;
        $this->init();
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
        if (!method_exists($this, $cmd.'Action')) {
            $res = 'No action '.$cmd.' exist';
        } elseif (!empty($_REQUEST['actionParameters'])){
            $res = call_user_func_array(
                array($this,$cmd.Action),
                $_REQUEST['actionParameters']
            );
        } else {
            $res = $this->{$cmd.'Action'}();
        }
        if (!empty($res) && is_string($res)) {
            $this->response->error('alert',$res);
        }
        return $this->response;
    }

    public function getDb()
    {
        return $this->db;
    }
    
    public function getParameter($key)
    {
        return (is_array($this->parameters) && array_key_exists($key,$this->parameters)) ? $this->parameters[$key] : null;
    }
    
    public function getResponse()
    {
        return $this->response;
    }
    
    public function getRequest()
    {
        return $this->request;
    }    
    
    abstract public function indexAction();
    
    abstract public function init();
    
    public function loadView($path, $params = array(), $return = false)
    {
        $params = array('Db' => $this->db, 'controller' => $this);
        $view = $this->response->getBuffer($path, $params);
        if ($return) {
            return $view;
        }
        $this->response->addContent($view);
    }
    
    public function run()
    {
        //if (!empty($_REQUEST[$this->actionKey])) {
        $cmd = filter_input(\INPUT_SERVER, 'HTTP_OSYNAPSY_ACTION');
        if (!empty($cmd)) {
            return $this->execAction($cmd);
        }        
        $this->setResponse(new HtmlResponse());
        $layoutPath = $this->request->get('app.layouts.'.$this->templateId);
        if (!empty($layoutPath)) {
            $this->response->template = $this->response->getBuffer($layoutPath, $this);            
        }
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
    
    public function setDbHandler($db)
    {
        $this->db = $db;
    }
    
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
}
