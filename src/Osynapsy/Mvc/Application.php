<?php
namespace Osynapsy\Mvc;

use Osynapsy\Kernel\Route;
use Osynapsy\Http\Request;
use Osynapsy\Db\DbFactory;

/**
 * Description of ApplicationController
 *
 * @author Pietro
 */
abstract class Application
{
    private $db;
    protected $route;    
    protected $request;
    protected $dbFactory;
    protected $exceptions = [];
    
    public final function __construct(Route &$route, Request &$request)
    {
        $this->route = $route;
        $this->request = $request;
        $this->loadDatasources();
        $this->init();
    }
    
    abstract protected function init();
    
    public function getDb($key = 0)
    {
        return $this->getDbFactory()->getConnection($key);
    } 
    
    public function getDbFactory()
    {
        return $this->dbFactory;
    }
    
    public function getRequest()
    {
        return $this->request;
    }
    
    public function getRoute()
    {
        return $this->route;
    }
    
    private function loadDatasources()
    {            
        $listDatasource = $this->getRequest()->search('db',"env.app.{$this->getRoute()->application}.datasources");
        $this->dbFactory = new DbFactory();
        foreach ($listDatasource as $datasource) {
            $connectionString = $datasource['@value'];
            $this->dbFactory->createConnection($connectionString);                       
        }
        $this->db = $this->dbFactory->getConnection(0);
    }
    
    public function run()
    {
        return true;
    }
    
    public function setException($e)
    {
        $this->exceptions[] = $e;
    }
}
