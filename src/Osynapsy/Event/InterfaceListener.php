<?php
namespace Osynapsy\Event;

use Osynapsy\Mvc\Controller;

/**
 * Description of Listener
 *
 * @author pietr
 */
interface InterfaceListener 
{
    public function __construct(Controller $controller);
    
    public function trigger();
}
