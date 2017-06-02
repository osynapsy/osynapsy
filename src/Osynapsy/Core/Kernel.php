<?php
namespace Osynapsy\Core;

use Osynapsy\Core\Network\Router;
use Osynapsy\Core\Network\Request;
use Osynapsy\Core\Driver\DbPdo;
use Osynapsy\Core\Driver\DbOci;

class Kernel
{
    private $repo = array(
        'xmlconfig' => array(),
        'events' => array(), 
        'layouts' => array()        
    );
    public $router;
    public $request;
    public $controller;
    public $appController;
    public $db = array();
    public $dba = array();

    public function __construct($fileconf, $requestRoute)
    {        
        $this->loadConfiguration($fileconf);
        $this->request = new Request($_GET, $_POST, array(), $_COOKIE, $_FILES, $_SERVER);
        $this->request->set(
            'app.parameters',
            $this->loadXmlConfig('/configuration/parameters/parameter', 'name', 'value')
        );
        $this->request->set(
            'app.layouts',
            $this->loadXmlConfig('/configuration/layouts/layout', 'name', 'path')
        );
        $this->request->set(
            'app.observers',
            $this->loadXmlConfig('/configuration/observers/observer', 0, 'event')
        );        
        $this->router = new Router(
            $requestRoute,
            $this->request
        );
        $this->loadRoutes(
            $this->repo['xmlconfig'],
            '/configuration/routes/route'
        );        
    }
    
    public function run()
    {
        if ($this->runAppController()) {
            $response = $this->runRouteController(
                $this->router->getRoute('controller')
            );
            if ($response !== false) {
                return $response;
            }
        }
        return $this->pageNotFound();
    }
    
    private function loadRoutes($xmlDocs, $path)
    {
        foreach ($xmlDocs as $appName => $xml) {
            foreach ($xml->xpath($path) as $e) {
                $id = (string) $e['id'];
                $url = (string) $e['path'];
                $ctl = (string) trim(str_replace(':', '\\', $e[0]));
                $tpl = (string) $e['template'];
                $this->router->addRoute($id, $url, $ctl, $tpl, $appName, $e->attributes());                
            }
        }
        $this->router->addRoute(
            'OsynapsyAssetsManager',
            '/__assets/osynapsy/?*',
            'Osynapsy\\Core\\Controller\\AssetLoader',
            '',
            'Osynapsy'
        );
    }
    
    private function runAppController()
    {
        $app = $this->router->getRoute('application');      
        if (empty($app)) {
            return true;
        }
        $this->loadDatasources("/configuration/app/$app/datasources/db");
        
        if (empty($this->repo['xmlconfig'][$app]['controller'])) {
            return true;
        }
        //If app has applicationController instance it before recall route controller;
        $classControllerApp = str_replace(':','\\',$this->repo['xmlconfig'][$app]['controller']);
        $this->appController = new $classControllerApp($this->dba, $this->router->getRoute());
        return $this->appController->run();
    }
    
    private function runRouteController($classController)
    {
        if (empty($classController)) {
            return false;
        }
        $this->controller = new $classController($this->request, $this->dba, $this->appController);
        return (string) $this->controller->run();
    }
    
    private function loadConfiguration($path)
    {
        if (!is_file($path)) {
            return;
        }
        $this->repo['xmlconfig'][0] = simplexml_load_file($path);
        if (!empty($this->repo['xmlconfig'][0]) && $this->repo['xmlconfig'][0]->app) {
            foreach ($this->repo['xmlconfig'][0]->app[0] as $e) {
                $appName = $e->getName();
                $appConf = filter_input(\INPUT_SERVER,'DOCUMENT_ROOT').'/../vendor/'.str_replace('_','/',$appName).'/etc/config.xml';                
                if (is_file($appConf)) {
                    $this->repo['xmlconfig'][$appName] = simplexml_load_file($appConf);
                }
            }
        }
    }

    private function loadDatasources($path = '/configuration/datasources/db')
    {
        foreach ($this->repo['xmlconfig'] as $xml) {            
            foreach ($xml->xpath($path) as $e) {                     
                $connectionStr = (string) $e[0];
                $connectionSha = sha1($connectionStr);
                if (array_key_exists($connectionSha, $this->db)) {
                    continue;
                }
                $this->db[$connectionSha] = $this->getDbConnection($connectionStr);               
                $this->db[$connectionSha]->connect();
                if (empty($this->dba)) {
                    $this->dba = $this->db[$connectionSha];
                }
            }
        }
    }

    private function getDbConnection($connectionString)
    {        
        if (strpos($connectionString, 'oracle') !== false) {
            return new DbOci($connectionString);
        } 
        return new DbPdo($connectionString);
    }
    
    public function loadXmlConfig($xpath, $kkey, $kval)
    {
        $result = array();
        foreach ($this->repo['xmlconfig'] as $xml) {
            foreach ($xml->xpath($xpath) as $e) {
                $result[$e[$kkey]->__toString()] = (isset($e[$kval]) ? $e[$kval]->__toString() : '');
            }
        }
        return $result;
    }
    
    public function loadRoute($xmlDocs, $path)
    {
        foreach ($xmlDocs as $appName => $xml) {
            foreach ($xml->xpath($path) as $e) {
                $id = (string) $e['id'];
                $url = (string) $e['path'];
                $ctl = (string) trim(str_replace(':', '\\', $e[0]));
                $tpl = (string) $e['template'];
                $this->router->addRoute($id, $url, $ctl, $tpl, $appName, $e->attributes());                
            }
        }        
    }
    
    public function pageNotFound($message = 'Page not found')
    {
        ob_clean();
        header('HTTP/1.1 404 Not Found');
        return $message;
    }
}
