<?php
namespace Osynapsy\Core\Kernel;

use Osynapsy\Core\Lib\Dictionary;
use Osynapsy\Core\Kernel\Router;
use Osynapsy\Core\Data\Driver\DbFactory;

/**
 * Description of Runner
 *
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class Runner
{
    private $env;
    private $router;
    private $applicationId;
    private $dbFactory;
    
    public function __construct(Dictionary &$env, Router $router)
    {
        $this->env = $env;
        $this->router = $router;
        $this->route = $this->router->getRoute();        
    }
    
    private function checks()
    {
        if (empty($this->route)) {
            throw new \Exception('No route to destination', 404);
        }
        if (empty($this->route['application'])) {
            throw new \Exception('No application defined', 405);
        }
        $this->applicationId = $this->route['application'];
    }
    
    public function run()
    {
        try {
            $this->checks();        
            $this->loadDatasources();
            $this->runApplicationController();
            $response = $this->runRouteController(
                $this->router->getRoute('controller')
            );
            if ($response !== false) {
                return $response;
            }
        } catch (\Exception $e) {
            switch($e->getCode()) {
                case '404':
                    return $this->pageNotFound();
                default :
                    return $this->pageOops($e->getMessage(), $e->getTrace());                 
            }            
        }        
    }
    
    private function runApplicationController()
    {        
        $applicationController = str_replace(':', '\\', $this->env->get("env.app.{$this->applicationId}.controller"));
        if (empty($applicationController)) {
            return true;
        }
        //If app has applicationController instance it before recall route controller;        
        $this->appController = new $applicationController(
            $this->dbFactory->getConnection(0), 
            $this->router->getRoute()
        );
        if (!$this->appController->run()) {
            throw \Exception('App not running (access denied)','501');
        }
    }
    
    private function runRouteController($classController)
    {
        if (empty($classController)) {
            throw \Exception('Route not found', '404');
        }
        $this->controller = new $classController($this->env, $this->dbFactory, $this->appController);
        return (string) $this->controller->run();
    }
    
    private function loadDatasources()
    {            
        $listDatasource = $this->env->search('db',"env.app.{$this->applicationId}.datasources");
        $this->dbFactory = new DbFactory();
        foreach ($listDatasource as $datasource) {
            $connectionString = $datasource['dbValue'];
            $this->dbFactory->buildConnection($connectionString);                       
        }
    }
    
    public function pageNotFound($message = 'Page not found')
    {
        ob_clean();
        header('HTTP/1.1 404 Not Found');
        return $message;
    }
    
    public function pageOops($message, $trace)
    {
        ob_clean();
        $body = '';
        foreach ($trace as $step) {
            $body .= '<tr>';
            $body .= '<td>'.$step['class'].'</td>';
            $body .= '<td>'.$step['function'].'</td>';
            $body .= '<td>'.$step['file'].'</td>';
            $body .= '<td>'.$step['line'].'</td>';            
            $body .= '</tr>';            
        }
        return <<<PAGE
            <style>
                * {font-family: Arial;} 
                div.container {margin: auto;} 
                td,th {font-size: 12px; font-family: Arial; padding: 3px; border: 0.5px solid silver}
            </style>
            <div class="container">       
                {$message}
                <table style="border-collapse: collapse; max-width: 1200px;">
                    <tr>
                        <th>Class</th>
                        <th>Function</th>
                        <th>File</th>
                        <th>Line</th>
                    </tr>
                    {$body}
                </table>
            </div>
PAGE;
                    
    }
}
