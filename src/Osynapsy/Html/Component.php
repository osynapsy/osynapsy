<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html;

use Osynapsy\Kernel;

/*
 * Master class component
 */
class Component extends Tag
{    
    protected static $require = [];    
    protected static $ids = [];
    protected $data = [];
    protected $__par = [];

    public function __construct($tag, $id = null)
    {
        parent::__construct($tag, $id);
        if (!empty($id)) {
            self::$ids[$id] = $this;
        }
    }
      
    protected function build()
    {
        $this->__build_extra__();
        return parent::build(-1);
    }
   
    /**
     * Overwrite this method if your component need of extra build operation
     *      
     * @return void
     */ 
    protected function __build_extra__()
    {
    }
    
    /**
     * Return component through his id
     * 
     * @param $id name of component to return;
     * @return object
     */
    public static function getById($id)
    {
        return array_key_exists($id, self::$ids) ? self::$ids[$id] : null;
    }
    
    /**
     * Return value of key from array
     * 
     * @param $nam name of value to return
     * @param $array array where search the value
     * @return mixed
     */
    public function getGlobal($nam, $array)
    {    
        if (strpos($nam,'[') === false){
            return array_key_exists($nam,$array) ? $array[$nam] : '';
        }
        $names = explode('[',str_replace(']','',$nam));
        $res = false;
        foreach($names as $nam) {
            if (!array_key_exists($nam,$array)) {
                continue;            
            }
            if (is_array($array[$nam])){ 
                $array = $array[$nam]; 
            } else { 
                $res = $array[$nam];
                break; 
            }
        }
        return $res;
    }
    
    /**
     * Return value of parameter
     * 
     * @param $key name of parameter to return;
     * @return mixed
     */
    public function getParameter($key)
    {
        return array_key_exists($key, $this->__par) ? $this->__par[$key] : null;
    }
    
    /**
     * Return list of required file (css, js etc) for correct initialization of component
     *      
     * @return array
     */
    public static function getRequire()
    {
        return self::$require;
    }
    
    public function nvl($a, $b)
    {
        return ( $a !== 0 && $a !== '0' && empty($a)) ? $b : $a;
    }

    private static function requireFile($file,$type)
    {
        if (!array_key_exists($type, self::$require)) {
            self::$require[$type] = [];
        }
        if (!in_array($file, self::$require[$type])) {
            $fullPath = in_array($file[0], ['/','h']) ? $file : '/assets/osynapsy/'.Kernel::VERSION.'/'.$file;
            self::$require[$type][] = $fullPath;
        }
    }
    
    /**
     * Append required js file to list of required file
     * 
     * @param $file web path of file;
     * @return void
     */    
    public static function requireJs($file)
    {
        self::requireFile($file, 'js');
    }
    
    /**
     * Append required js code to list of required initialization
     * 
     * @param $code js code to append at html page;
     * @return void
     */
    public static function requireJsCode($code)
    {
        self::requireFile($code, 'jscode');
    }
    
    /**
     * Append required css to list of required File
     * 
     * @param $file web path of css file;
     * @return void
     */
    public static function requireCss($file)
    {
        self::requireFile($file, 'css');
    }
    
    /**
     * Append css class to component class attribute
     * 
     * @param $class name of css class to add;
     * @return $this
     */
    public function setClass($class, $append = true)
    {
        return $this->att('class',' '.$class, $append);
    }
    
    /**
     * Set data internal property of component
     * 
     * @param array $data set of data;     
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
    
    /**
     * Set value for internal parameter of component
     * 
     * @param string $key name of the parameter
     * @param string $value value to assign parameter
     * @return $this
     */
    public function setParameter($key, $value = null)
    {
        $this->__par[$key] = $value;
        return $this;
    }
    
    /**
     * Set action to recall via ajax
     * 
     * @param string $action name of the action without Action final
     * @param string $parameters parameters list (comma separated) to pass action
     * @return $this
     */
    public function setAction($action, $parameters = null, $class = 'click-execute')
    {
        $this->setClass($class)
             ->att('data-action',$action);
        if (!empty($parameters) || $parameters === 0 || $parameters === '0') {
            $this->att('data-action-parameters', $parameters);
        }
        return $this;
    }
    
    /**
     * Set placeholder attribute
     * 
     * @param string $placeholder placeholder value    
     * @return $this
     */    
    public function setPlaceholder($placeholder)
    {
        $this->att('placeholder', $placeholder);
        return $this;
    }
}
