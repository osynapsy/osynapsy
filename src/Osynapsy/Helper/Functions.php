<?php
use Osynapsy\Kernel;
use Osynapsy\Helper\AutoWire;
use Osynapsy\Application\ApplicationInterface;
use Osynapsy\Database\Driver\DboInterface;

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
function route($routeId = null, ?array $parameters = null, ?array $getParams = [])
{
    $route = request()->getRoute($routeId);
    return is_null($parameters) ? $route : $route->getUrl($parameters, $getParams);
}

/**
 * Return an instance of AutoWire class
 *
 * @param array $handles array of object instanced used how handles from autowire
 * @return AutoWire
 */
function autowire(array $handles = [])
{
    return new AutoWire($handles);
}

/**
 * Return an instance of App class
 *
 * @return Dbo
 */
function app()
{
    return AutoWire::getHandle(ApplicationInterface::class);
}

/**
 * Return an instance of Dbo class
 *
 * @return Dbo
 */
function dbo()
{
    return AutoWire::getHandle(DboInterface::class);
}

/**
 * Redirect client browser to $rawdirection url
 * if first character of $rawdestination is a '#' build a route.
 *
 * @param string $rawdestination
 * @param array $getParams
 * @param array $routeParams
 */
function redirect(string $rawdestination, array $getParams = [], array $routeParams = [])
{
    $destination = ($rawdestination[0] === '#') ? route(ltrim($rawdestination, '#'), $routeParams) : $rawdestination;    
    header(sprintf('Location: %s%s', $destination, !empty($getParams) ? '?' . http_build_query($getParams) : ''));
}
