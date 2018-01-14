<?php
namespace Osynapsy\Core\Kernel;

use Osynapsy\Core\Lib\Dictionary;
/**
 * 
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class RouteCollection extends Dictionary
{
    public function __construct()
    {
        parent::__construct(
            array(
                'routes' => array()
            )
        );
    }
    
    public function addRoute($id, $route, $controller, $templateId = null)
    {
        $this->set(
            'routes.'.$id,
            array(
                'path' => $route,
                'controller' => $controller,
                'templateId' => $templateId
            )
        );
    }
}
