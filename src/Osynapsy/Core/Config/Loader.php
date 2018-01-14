<?php
namespace Osynapsy\Core\Config;

use Osynapsy\Core\Lib\Dictionary;

/**
 * Description of LoaderXml
 *
 * @author Pietro Celeste <p.celeste@osynapsy.org>
 */
class Loader
{    
    private $repo;
    private $path;
    
    public function __construct($path)
    {            
        $this->path = $path;
        $this->repo = new Dictionary();
        $this->repo->set('configuration', $this->load());
        $this->loadAppConfiguration();
    }
    
    private function loadDir($path)
    {
        return [];
    }

    private function loadFile($path)
    {
        $xml = new \SimpleXMLIterator($path,null,true);
        return $this->parseXml($xml);
    }
    
    private function loadAppConfiguration()
    {
        $apps = $this->repo->get('configuration.app');
        foreach(array_keys($apps) as $app) {
            $path = '../vendor/'.str_replace("_", "/", $app).'/etc/config.xml';
            if (is_file($path)) {
                $this->repo->append('configuration.app.'.$app, $this->loadFile($path));
            }
        }
    }
    
    private function parseXml($xml, &$array = [])
    {                                
        for($xml->rewind(); $xml->valid(); $xml->next() ) {
            $key = $xml->key();
            if (!array_key_exists($key, $array)) {
                $array[$key] = [];
            }
            if ($xml->hasChildren()){
                $this->parseXml($xml->current(), $array[$key]);
                continue;
            }
            
            $raw = (array) $xml->current()->attributes();
            if (empty($raw)) {
               $array[$key] =  trim(strval($xml->current()));
               continue;
            }
            $element = [
                $key.'Value' => trim(strval($xml->current()))
            ];
            $element += $raw['@attributes'];
            $array[$key][] = $element;
        }
        return $array;
    }
    
    private function load()
    {    
        $array = [];
        if (is_file($this->path)) {
            $array = $this->loadFile($this->path);
        } elseif (is_dir($this->path)) {
            $array = $this->loadDir();        
        }
        return $array;
    }
    
    public function get($key = '')
    {
        return $this->repo->get('configuration'.(empty($key) ? '' : ".{$key}"));
    }
            
    public function search($keySearch, $searchPath = null, $debug = false)
    {
        $fullPath = 'configuration';
        if (!empty($searchPath)) {
            $fullPath .= '.'.$searchPath;
        }
        if ($debug) {
            var_dump($fullPath);
        }
        return $this->repo->search($keySearch, $fullPath);
    }
}
