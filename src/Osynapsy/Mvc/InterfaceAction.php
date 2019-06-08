<?php
namespace Osynapsy\Mvc;


/**
 * Description of Action
 *
 * @author pietr
 */
interface InterfaceAction 
{   
    public function run();
    
    public function setController(Controller $controller);
}
