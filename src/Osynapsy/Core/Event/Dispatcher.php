<?php
namespace Osynapsy\Core\Event;

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
        die('ciao'.print_r($listeners,'true'));
    }
    
    private function init()
    {
        $this->init = true;
    }
}
