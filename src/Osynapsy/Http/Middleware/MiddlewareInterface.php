<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Http\Middleware;

use Osynapsy\Kernel\Route;
use Osynapsy\Http\Request;
use Osynapsy\Http\Response\Base as Response;
use Osynapsy\Database\Driver\DboInterface;
use Osynapsy\Database\DboFactory;

interface MiddlewareInterface
{
    public function __construct(Route &$route, Request &$request);

    public function getComposer();

    public function getDb(int $key = 0) : DboInterface;

    public function getDbFactory() : DboFactory;

    public function getRequest($key = null);

    public function getResponse() : Response;

    public function getRoute() : Route;

    public function execute() : string;

    public function setComposer($composer);

    public function setResponse(Response $response);
}
