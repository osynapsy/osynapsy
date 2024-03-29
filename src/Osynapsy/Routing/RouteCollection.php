<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Routing;

use Osynapsy\DataStructure\Dictionary;

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
        $this->set('routes.'.$newRoute->id, $newRoute);
    }
}
