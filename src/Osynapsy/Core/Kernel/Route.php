<?php
namespace Osynapsy\Core\Kernel;

/**
 * Description of Route
 *
 * @author Peter
 */
class Route 
{
    private $route = [
        'id' => null,
        'uri' => null,
        'application' => null,
        'controller' => null,
        'template' => null,
        'attributes' => []
    ];
    
    public function __construct($id = '', $uri = '', $application = '', $controller = '', $template = '', $attributes = [])
    {
        $this->id = empty($id) ? sha1($uri) : $id;
        $this->uri = $uri;
        $this->application = trim($application);
        $this->controller = trim(str_replace(':','\\',$controller));
        $this->template = $template;
        $this->attributes = $attributes;
    }
    
    public function __get($key)
    {
        return array_key_exists($key, $this->route) ? $this->route[$key] : null;
    }
    
    public function __set($key, $value)
    {
        $this->route[$key] = $value;
    }
    
    public function __toString()
    {
        return $this->id;
    }
}
