<?php
namespace Osynapsy\Http;

use Osynapsy\Kernel\Route;
use Osynapsy\Http\Request;
use Osynapsy\Http\RequestHandler\RequestHandler;
use Osynapsy\Http\Response\Base as Response;
use Osynapsy\Http\Response\JsonOsynapsy as JsonOsynapsyResponse;
use Osynapsy\Http\Response\Html as HtmlResponse;
use Osynapsy\Http\Response\Xml as XmlResponse;
use Osynapsy\Database\DboFactory;

/**
 * Description of App
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class App
{
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
        $requestHandler = new RequestHandler();
        $this->setResponse($requestHandler->handle($this->getRequest()));
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
    public function getDb(int $key = 0) : \Osynapsy\Database\Driver\InterfaceDbo
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
    public function getResponse() : Response
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
        $classController = $this->route->controller;
        $controller = new $classController($this->getRequest(), $this);
        return (string) $controller->run(
            filter_input(\INPUT_SERVER, 'HTTP_OSYNAPSY_ACTION'),
            filter_input(\INPUT_POST , 'actionParameters', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?? []
        );
    }

    /**
     * Set current Response
     *
     * @return Response
     */
    public function setResponse(Response $response)
    {
        return $this->response = $response;
    }

    public function setComposer($composer)
    {
        $this->composer = $composer;
    }
}
