<?php
namespace Osynapsy\Mvc\Action;

use Osynapsy\Mvc\Controller;

/**
 * Description of Action
 *
 * @author pietr
 */
interface InterfaceAction 
{   
    public function __construct(array $parameters);
    
    public function execute();
    
    public function setController(Controller $controller);
}
