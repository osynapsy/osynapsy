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

/**
 * Description of Runner
 *
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class Starter
{
    private $env;
    private $route;
    private $appController;    
    
    public function __construct(Dictionary &$env, Route $currentRoute)
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
    
    private function dispatchKernelException(KernelException $e)
    {
        switch($e->getCode()) {
            case '404':
                return $this->pageNotFound($e->getMessage());
            case '501':
                return $this->pageOops($e->getMessage());
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
        $this->appController = new $applicationController($this->route, $this->env);
        if (!$this->appController->run()) {
            throw new KernelException('Access denied','501');
        }
    }
    
    private function runRouteController($classController)
    {
        if (empty($classController)) {
            throw new KernelException('Route not found', '404');
        }
        $this->controller = new $classController($this->env, $this->appController);
        return (string) $this->controller->run();
    }
    
    public function run()
    {
        try {
            $this->checks();
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
    
    public function pageNotFound($message = 'Page not found')
    {
        ob_clean();
        header('HTTP/1.1 404 Not Found');
        return $message;
    }
    
    public function pageOops($message, $trace = [])
    {
        ob_clean();
        if (filter_input(\INPUT_SERVER, 'HTTP_OSYNAPSY_ACTION')) {
            return $message;
        }
        $body = '';
        if (!empty($trace)) {
            $body .= '<table style="border-collapse: collapse;">';
            $body .= '<tr>';
            $body .= '<th>Class</th>';
            $body .= '<th>Function</th>';
            $body .= '<th>File</th>';
            $body .= '<th>Line</th>';
            $body .= '</tr>';
            foreach ($trace as $step) {
                $body .= '<tr>';
                $body .= '<td>'.(!empty($step['class']) ? $step['class'] : '&nbsp;').'</td>';
                $body .= '<td>'.(!empty($step['function']) ? $step['function'] : '&nbsp;').'</td>';
                $body .= '<td>'.(!empty($step['file']) ? $step['file'] : '&nbsp;').'</td>';
                $body .= '<td>'.(!empty($step['line']) ? $step['line'] : '&nbsp;').'</td>';            
                $body .= '</tr>';            
            }
            $body .= '</table>';
        }
        return <<<PAGE
            <div class="container">       
                <div class="message">{$message}</div>
                {$body}
            </div>
            <style>
                * {font-family: Arial;}
                body {margin: 0px;}
                div.container {margin: 0px; max-width: 1024px; margin: auto;}
                table {width: 100%; margin-top: 20px;}
                .message {background-color: #B0413E; color: white; padding: 10px; font-weight: bold;}
                td,th {font-size: 12px; font-family: Arial; padding: 3px; border: 0.5px solid silver}
            </style>
PAGE;
                    
    }
}
