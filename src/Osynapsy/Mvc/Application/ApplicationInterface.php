<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc\Application;

use Osynapsy\Kernel\Route;
use Osynapsy\Http\Request;
use Osynapsy\Http\Response\Base as Response;

interface ApplicationInterface
{
    public function __construct(Route &$route, Request &$request);

    public function getComposer();

    public function getDb(int $key = 0) : \Osynapsy\Database\Driver\InterfaceDbo;

    public function getDbFactory() : \Osynapsy\Database\DboFactory;

    public function getRequest($key = null);

    public function getResponse() : Response;

    public function getRoute() : Route;

    public function execute() : string;

    public function setComposer($composer);

    public function setResponse(Response $response);
}
