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
    }
     

    private function loadDir($path)
    {
        return [];
    }

    private function loadFile($path)
    {
        $xml = simplexml_load_file($path);
        $json = json_encode($xml);
        return json_decode($json,TRUE);
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
    
    public function get()
    {
        return $this->repo->get('configuration');
    }
            
    public function search($keySearch)
    {
        return $this->repo->search($keySearch);
    }
}
