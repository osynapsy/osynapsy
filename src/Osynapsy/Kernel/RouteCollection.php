<?php
namespace Osynapsy\Kernel;

use Osynapsy\Data\Dictionary;

/**
 * 
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class RouteCollection extends Dictionary
{
    public function __construct()
    {
        parent::__construct([
            'routes' => []
        ]);
    }
    
    public function addRoute($id, $route, $application, $controller, $templateId = null, $attributes = [])
    {
        $newRoute = new Route($id, $route, $application, $controller, $templateId, $attributes);        
        $this->set('routes.'.$newRoute, $newRoute);
    }
}
