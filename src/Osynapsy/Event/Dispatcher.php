<?php
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
