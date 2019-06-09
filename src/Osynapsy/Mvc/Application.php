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
class Application
{
    private $db;
    protected $route;    
    protected $request;
    protected $dbFactory;
    protected $exceptions = [];
    
    /**
     * Constructor of application launcher.
     * 
     * @param Route $route
     * @param Request $request
     */
    public final function __construct(Route &$route, Request &$request)
    {
        $this->route = $route;
        $this->request = $request;
        $this->loadDatasources();
        $this->init();
    }
    
    /**
     * Return db connection request
     * 
     * @param int $key
     * @return \Osynapsy\Db\Driver\InterfaceDbo
     */
    public function getDb(int $key = 0) : \Osynapsy\Db\Driver\InterfaceDbo
    {
        return $this->getDbFactory()->getConnection($key);
    } 
    
    /**
     * Return DbFactory
     * 
     * @return DbFactory
     */
    public function getDbFactory() : DbFactory
    {
        return $this->dbFactory;
    }
    
    /**
     * Return current Request
     * 
     * @return Request
     */
    public function getRequest() : Request
    {
        return $this->request;
    }
    
    /**
     * Return current route
     * 
     * @return Route
     */
    public function getRoute() : Route
    {
        return $this->route;
    }
    
    protected function init()
    {
    }
    
    /**
     * Load datasources configurated into instance configuration file
     */
    private function loadDatasources()
    {            
        $listDatasource = $this->getRequest()->search('db',
            "env.app.{$this->getRoute()->application}.datasources"
        );
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
    
    /**
     * Run request action from the user
     * 
     * @return string
     * @throws \Osynapsy\Kernel\KernelException
     */
    public function runAction() : string
    {        
        if (empty($this->route) || !$this->route->controller) {
            throw new \Osynapsy\Kernel\KernelException('Route not found', 404);
        }
        $classController = $this->route->controller;
        $controller = new $classController($this->getRequest(), $this);
        return (string) $controller->run(
            filter_input(\INPUT_SERVER, 'HTTP_OSYNAPSY_ACTION'),
            filter_input(\INPUT_POST , 'actionParameters', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?? []
        );
    }
}
