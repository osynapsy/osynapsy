<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Event;

/**
 * Description of Dispatcher
 *
 * @author Peter
 */
class Dispatcher 
{
    private $controller;
    private $listeners = [];
    
    public function __construct($controller)
    {
        $this->controller = $controller;
    }
    
    public function dispatch(Event $event)
    {
        $listeners = $this->getController()->getRequest()->get('listeners');
        if (empty($listeners)) {
            return;
        }
        foreach($listeners as $listener => $eventId) {
            if ($eventId != $event->getId()) {
                continue;
            }
            $listenerId = '\\'.trim(str_replace(':','\\',$listener));
            $this->getListener($listenerId)->trigger($event);
        }
    }
    
    private function getController()
    {
        return $this->controller;
    }
    
    private function getListener($id)
    {                
        if (empty($this->listeners[$id])) {
            $this->listeners[$id] = new $id($this->controller);
        }
        return $this->listeners[$id];
    }
}
