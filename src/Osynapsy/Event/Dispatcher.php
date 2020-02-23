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
        $this->loadGlobalListeners($event->getId());
        $this->triggerEvent($event);
    }
    
    private function triggerEvent(Event $event)
    {
        if (empty($this->listeners[$event->getId()])) {
            return;
        }
        foreach($this->listeners[$event->getId()] as $listener) {                        
            $listener->trigger($event);
        }
    }
    
    private function loadGlobalListeners($eventId)
    {
        $listeners = $this->getController()->getRequest()->get('listeners');
        if (empty($listeners)) {
            return;
        }
        foreach($listeners as $listener => $listenerEventId) {
            if ($listenerEventId != $eventId) {
                continue;
            }
            if (!array_key_exists($eventId, $this->listeners2)) {
                $this->listeners[$eventId] = [];
            }            
            $listenerId = '\\'.trim(str_replace(':','\\',$listener));
            $this->listeners[$eventId][] = new $listenerId($this->getController());
        }
    }
    
    private function getController()
    {
        return $this->controller;
    }        
    
    public function addListener(callable $trigger, array $eventIDs)
    {
        $listener = new class($this->getController()) implements InterfaceListener
        {            
            private $controller;
            private $trigger;
            
            public function __construct($controller)
            {
                $this->controller = $controller;
            }
                        
            public function setTrigger(callable $callable)
            {
                $this->trigger = $callable;               
            }
            
            public function trigger(Event $event)
            {
                $trigger = $this->trigger;
                $trigger($event);
            }
        };
        $listener->setTrigger($trigger);
        foreach ($eventIDs as $eventId) {
            if (!array_key_exists($eventId, $this->listeners)) {
                $this->listeners[$eventId] = [];
            }
            $this->listeners[$eventId][] = $listener;
        }
    }
}
