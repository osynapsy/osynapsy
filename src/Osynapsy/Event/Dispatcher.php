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
    public $controller;
    private $init = false;
    
    public function __construct($controller)
    {
        $this->controller = $controller;
    }
    
    public function dispatch(Event $event)
    {
        if (!$this->init) {
            $this->init();
        }
        $listeners = $this->controller->getRequest()->get('listeners');
        if (empty($listeners)) {
            return;
        }
        foreach($listeners as $listener => $eventId) {
            if ($eventId != $event->getId()) {
                continue;
            }
            $listenerClass = '\\'.trim(str_replace(':','\\',$listener));
            $handle = new $listenerClass($this->controller);
            $this->trigger($handle);
        }
    }
    
    private function trigger(InterfaceListener $listener)
    {                
        $listener->trigger();
    }
    
    private function init()
    {
        $this->init = true;
    }
}
