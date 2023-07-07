<?php
use Osynapsy\Kernel;

/**
 * If $elementPath is null return request Object else return required element of request
 *
 * @param string $elementPath
 * @return mixed
 */
function request($elementPath = null)
{
    return is_null($elementPath) ? Kernel::$request : Kernel::$request->get($elementPath);
}

/**
 * if routeId parameter is null return current route else return ruote with routeId.
 * If array parameters is passed build relative url of the specified route
 * 
 * @param string $routeId
 * @param array|null $parameters
 * @return mixed
 */
function route($routeId = null, ?array $parameters = null)
{
    $route = request()->getRoute($routeId);
    return is_null($parameters) ? $route : $route->getUrl($parameters);
}

function autowiring(array $handles = [])
{
    $autowiring = new Osynapsy\Mvc\Application\AutoWiring();
    foreach($handles as $handle) {
        $autowiring->addHandle($handle);
    }
    return $autowiring;
}
