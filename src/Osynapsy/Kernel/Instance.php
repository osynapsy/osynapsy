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
class Instance
{
    private $request;
    private $route;
    private $appController;    
    
    public function __construct(Dictionary &$request, Route $currentRoute)
    {
        $this->request = $request;
        $this->route = $currentRoute;        
    }
    
    private function checks()
    {
        if (!$this->route->controller) {
            throw new KernelException('No route to destination ('.$this->request->get('server.REQUEST_URI').')', 404);
        }
        if (!$this->route->application) {
            throw new KernelException('No application defined', 405);
        }
    }
    
    public function run()
    {
        $this->checks();
        $this->runApplication();
        $response = $this->runController(
            $this->route->controller
        );
        if ($response !== false) {
            return $response;
        } 
    }
    
    private function runApplication()
    {
        $applicationController = str_replace(':', '\\', $this->request->get("env.app.{$this->route->application}.controller"));
        if (empty($applicationController)) {
            return true;
        }
        //If app has applicationController instance it before recall route controller;        
        $this->appController = new $applicationController($this->route, $this->request);
        if (!$this->appController->run()) {
            throw new ApplicationException('Access denied','501');
        }
    }
    
    private function runController($classController)
    {
        if (empty($classController)) {
            throw new KernelException('Route not found', '404');
        }
        $this->controller = new $classController($this->request, $this->appController);
        return (string) $this->controller->run();
    }        
}
