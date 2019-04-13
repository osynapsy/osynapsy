<?php
namespace Osynapsy\Mvc;

use Osynapsy\Kernel\Route;
use Osynapsy\Http\Request;

/**
 * Description of ApplicationController
 *
 * @author Pietro
 */
abstract class ApplicationController
{
    protected $db;
    protected $route;    
    protected $request;
    protected $exceptions = [];
    
    public final function __construct($db, Route &$route, Request &$request)
    {
        $this->db = $db;
        $this->route = $route;
        $this->request = $request;
        $this->init();
    }
    
    abstract protected function init();
    
    public function run()
    {
        return true;
    }
    
    public function setException($e)
    {
        $this->exceptions[] = $e;
    }
    
    public function getRequest()
    {
        return $this->request;
    }
    
    public function getRoute()
    {
        return $this->route;
    }
    
    public function getDb()
    {
        return $this->db;
    }
}
    