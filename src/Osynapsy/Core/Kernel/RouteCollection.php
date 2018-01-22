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
    
    public function addRoute($id, $route, $application, $controller, $templateId = null, $attributes = array())
    {
        $this->set(
            'routes.'.(empty($id) ? sha1($route) : $id),
            array(
                'path' => $route,
                'application' => $application,
                'controller' => $controller,
                'templateId' => $templateId,
                'attributes' => $attributes,
                'parameters' => []
            )
        );
    }
}
