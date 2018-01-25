<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Kernel;

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
        'template' => null
    ];
    
    public function __construct($id = '', $uri = '', $application = '', $controller = '', $template = '',array $attributes = [])
    {
        $this->id = empty($id) ? sha1($uri) : $id;
        $this->uri = $uri;
        $this->application = trim($application);
        $this->controller = trim(str_replace(':','\\',$controller));
        $this->template = $template;
        $this->route += $attributes;
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
