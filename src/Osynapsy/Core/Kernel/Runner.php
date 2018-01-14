<?php
namespace Osynapsy\Core\Kernel;

use Osynapsy\Core\Lib\Dictionary;
use Osynapsy\Core\Kernel\Router;
use Osynapsy\Core\Driver\DbPdo;
use Osynapsy\Core\Driver\DbOci;

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
    private $databaseConnections = [];
    
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
            array_values($this->databaseConnections)[0], 
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
        $this->controller = new $classController(
            $this->env, 
            array_values($this->databaseConnections)[0], 
            $this->appController
        );
        return (string) $this->controller->run();
    }
    
    private function loadDatasources()
    {            
        $listDatasource = $this->env->search('db',"env.app.{$this->applicationId}.datasources");
        foreach ($listDatasource as $datasource) {
            $connectionStr = $datasource['dbValue'];
            $connectionSha1 = sha1($connectionStr);
            if (array_key_exists($connectionSha1, $this->databaseConnections)) {
                continue;
            }
            $this->databaseConnections[$connectionSha1] = $this->getDatabaseConnection($connectionStr);               
            $this->databaseConnections[$connectionSha1]->connect();                        
        }
    }
    
    private function getDatabaseConnection($connectionString)
    {        
        if (strpos($connectionString, 'oracle') !== false) {
            return new DbOci($connectionString);
        } 
        return new DbPdo($connectionString);
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
