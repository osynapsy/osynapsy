<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Kernel;

use Osynapsy\Data\Dictionary;
use Osynapsy\Db\DbFactory;

/**
 * Description of Runner
 *
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class Runner
{
    private $env;
    private $route;
    private $dbFactory;
    private $appController;
    
    public function __construct(Dictionary &$env, $currentRoute)
    {
        $this->env = $env;
        $this->route = $currentRoute;
    }
    
    private function checks()
    {
        if (!$this->route->controller) {
            throw new KernelException('No route to destination ('.$this->env->get('server.REQUEST_URI').')', 404);
        }
        if (!$this->route->application) {
            throw new KernelException('No application defined', 405);
        }
    }
    
    public function run()
    {
        try {
            $this->checks();        
            $this->loadDatasources();
            $this->runApplicationController();
            $response = $this->runRouteController(
                $this->route->controller
            );
            if ($response !== false) {
                return $response;
            }
        } catch (KernelException $e) {
            return $this->dispatchKernelException($e);
        } catch(\Exception $e) {
            return $this->pageOops($e->getMessage(), $e->getTrace()); 
        }   
    }
    
    private function dispatchKernelException(KernelException $e)
    {
        switch($e->getCode()) {
            case '404':
                return $this->pageNotFound($e->getMessage());
            default :
                return $this->pageOops($e->getMessage(), $e->getTrace());                 
        }
    }
    
    private function runApplicationController()
    {        
        $applicationController = str_replace(':', '\\', $this->env->get("env.app.{$this->route->application}.controller"));
        if (empty($applicationController)) {
            return true;
        }
        //If app has applicationController instance it before recall route controller;        
        $this->appController = new $applicationController(
            $this->dbFactory->getConnection(0), 
            $this->route
        );
        if (!$this->appController->run()) {
            throw new KernelException('App not running (access denied)','501');
        }
    }
    
    private function runRouteController($classController)
    {
        if (empty($classController)) {
            throw new KernelException('Route not found', '404');
        }
        $this->controller = new $classController($this->env, $this->dbFactory, $this->appController);
        return (string) $this->controller->run();
    }
    
    private function loadDatasources()
    {            
        $listDatasource = $this->env->search('db',"env.app.{$this->route->application}.datasources");
        $this->dbFactory = new DbFactory();
        foreach ($listDatasource as $datasource) {
            $connectionString = $datasource['@value'];
            $this->dbFactory->createConnection($connectionString);                       
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
            $body .= '<td>'.(!empty($step['class']) ? $step['class'] : '&nbsp;').'</td>';
            $body .= '<td>'.(!empty($step['function']) ? $step['function'] : '&nbsp;').'</td>';
            $body .= '<td>'.(!empty($step['file']) ? $step['file'] : '&nbsp;').'</td>';
            $body .= '<td>'.(!empty($step['line']) ? $step['line'] : '&nbsp;').'</td>';            
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
