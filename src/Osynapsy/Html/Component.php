<?php
namespace Osynapsy\Html;

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
    
    protected function __build_extra__()
    {
    }
            
    public static function getById($id)
    {
        return array_key_exists($id, self::$ids) ? self::$ids[$id] : null;
    }
    
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
    
    public function getParameter($key)
    {
        return array_key_exists($key, $this->__par) ? $this->__par[$key] : null;
    }
    
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
            self::$require[$type][] = $file;
        }
    }
    
    public static function requireJs($file)
    {
        self::requireFile($file, 'js');
    }
    
    public static function requireJsCode($code)
    {
        self::requireFile($code, 'jscode');
    }
    
    public static function requireCss($file)
    {
        self::requireFile($file, 'css');
    }
    
    public function setClass($class, $append = true)
    {
        return $this->att('class',' '.$class, $append);
    }
    
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
    
    public function setParameter($key, $value = null)
    {
        $this->__par[$key] = $value;
    }
}
