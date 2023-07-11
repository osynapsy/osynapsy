<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Application;

use Osynapsy\Routing\Route;
use Osynapsy\Http\Request;
use Osynapsy\Http\Response\ResponseInterface;
use Osynapsy\Http\Response\JsonOsynapsy as JsonOsynapsyResponse;
use Osynapsy\Http\Response\Html as HtmlResponse;
use Osynapsy\Http\Response\Xml as XmlResponse;
use Osynapsy\Database\DboFactory;
use Osynapsy\Database\Driver\DboInterface;
use Osynapsy\Action\ActionRunner;

/**
 * Application controller is the main controller of app.
 * Kernel locate application controller and pass the request and route to load.
 * Application controller analyze permessions and load route control or raise
 * exception if current user don't has access to request route.
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class BaseApplication implements ApplicationInterface
{
    const DEFAULT_CONTROLLER_METHOD = 'index';

    protected $db;
    protected $route;
    protected $request;
    protected $composer;
    protected $response;
    protected $dbFactory;
    protected $exceptions = [];

    /**
     * Constructor of application launcher.
     *
     * @param Route $route
     * @param Request $request
     */
    public final function __construct(Route &$route, Request &$request)
    {
        $this->route = $route;
        $this->request = $request;
        $this->initDatasources();
        $this->initResponse();
        $this->init();
    }

    /**
     * Initialize response according with request contentType.
     *
     */
    protected function initResponse()
    {
        $accept = $this->getRequest()->getAcceptedContentType();
        if (empty($accept)) {
            $accept = ['text/html'];
        }
        switch($accept[0]) {
            case 'text/json':
            case 'application/json':
            case 'application/json-osynapsy':
                ini_set("xdebug.overload_var_dump", "off");
                $this->setResponse(new JsonOsynapsyResponse());
                break;
            case 'application/xml':
                ini_set("xdebug.overload_var_dump", "off");
                $this->setResponse(new XmlResponse());
                break;
            default:
                $this->setResponse(new HtmlResponse());
                break;
        }
    }

    /**
     * Init datasources configurated in instance configuration file
     */
    protected function initDatasources()
    {
        $listDatasource = $this->getRequest()->search('db',
            "env.app.{$this->getRoute()->application}.datasources"
        );
        $this->dbFactory = new DboFactory();
        foreach ($listDatasource as $datasource) {
            $connectionString = $datasource['@value'];
            $this->dbFactory->createConnection($connectionString);
        }
        $this->db = $this->dbFactory->getConnection(0);
    }

    protected function init()
    {
    }

    /**
     * Return db connection request
     *
     * @param int $key
     * @return \Osynapsy\Database\Driver\InterfaceDbo
     */
    public function getDb(int $key = 0) : ?DboInterface
    {
        return $this->getDbFactory()->getConnection($key);
    }

    public function getComposer()
    {
        return $this->composer;
    }

    /**
     * Return DbFactory
     *
     * @return DbFactory
     */
    public function getDbFactory() : DboFactory
    {
        return $this->dbFactory;
    }

    /**
     * Return current Request object
     *
     * @return Request
     */
    public function getRequest($key = null)
    {
        return is_null($key) ? $this->request : $this->request->get($key);
    }

    /**
     * Return current Response object
     *
     * @return Response
     */
    public function getResponse() : ResponseInterface
    {
        return $this->response;
    }

    /**
     * Return current route
     *
     * @return Route
     */
    public function getRoute() : Route
    {
        return $this->route;
    }

    /**
     * Execute request action from the user
     *
     * @return string
     * @throws \Osynapsy\Kernel\KernelException
     */
    public function execute() : string
    {
        if (empty($this->route) || !$this->route->controller) {
            throw new \Osynapsy\Kernel\KernelException('Route not found', 404);
        }
        $actionId = filter_input(\INPUT_SERVER, 'HTTP_OSYNAPSY_ACTION');
        $actionParameters = filter_input(\INPUT_POST , 'actionParameters', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        list($controller, $defaultAction) = $this->controllerFactory($this->route->controller, $this);
        return (string) $this->runAction($controller, $defaultAction, $actionId, $actionParameters ?? []);
    }

    protected function controllerFactory($classController, $appController)
    {
        $rawClass = strpos($classController, '::') === false ? $classController.'::'.self::DEFAULT_CONTROLLER_METHOD : $classController;
        list($class, $defaultAction) = explode('::', $rawClass, 2);
        return [new $class($this->getRequest(), $appController), $defaultAction ?? self::DEFAULT_CONTROLLER_METHOD];
    }

    protected function runAction($controller, $defaultAction, $actionId, array $actionParams)
    {
        return (new ActionRunner($controller))->run($defaultAction, $actionId, $actionParams);
    }

    /**
     * Set current Response
     *
     * @return Response
     */
    public function setResponse(ResponseInterface $response)
    {
        return $this->response = $response;
    }

    public function setComposer($composer)
    {
        $this->composer = $composer;
    }
}
