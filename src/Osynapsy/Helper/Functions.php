<?php
use Osynapsy\Kernel;


function request()
{
    return Kernel::$request;
}

function route($routeId = null, ?array $parameters = null)
{
    $route = request()->getRoute($routeId);
    return is_null($parameters) ? $route : $route->getUrl($parameters);
}
