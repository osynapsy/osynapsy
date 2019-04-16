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
use Osynapsy\Kernel\KernelException;
use Osynapsy\Mvc\ApplicationException;

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
    
    private function runApplicationController()
    {
        $applicationController = str_replace(':', '\\', $this->env->get("env.app.{$this->route->application}.controller"));
        if (empty($applicationController)) {
            return true;
        }
        //If app has applicationController instance it before recall route controller;        
        $this->appController = new $applicationController($this->route, $this->env);
        if (!$this->appController->run()) {
            throw new ApplicationException('Access denied','501');
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
        $this->checks();
        $this->runApplicationController();
        $response = $this->runRouteController(
            $this->route->controller
        );
        if ($response !== false) {
            return $response;
        } 
    }
}
