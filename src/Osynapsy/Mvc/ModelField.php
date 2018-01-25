<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc;

class ModelField 
{
    private $repo = array(
        'fixlength' => null,
        'is_pk' => false,
        'maxlength' => null,
        'minlength' => null,
        'nullable' => true, 
        'readonly' => false,
        'rawvalue' => null,
        'unique' => false,
        'value' => null,
        'defaultValue' => null,
        'uploadDir' => '/upload'
    );
    private $model;    
    public $type;
    
    public function __construct($model, $nameOnDb, $nameOnView, $type = 'string')
    {
        $this->model = $model;
        $this->name = $nameOnDb;
        $this->html = $nameOnView;
        $this->type = $type;
    }

    public function __get($key)
    {
        return array_key_exists($key,$this->repo) ? $this->repo[$key] : null;
    }

    public function __set($key, $value)
    {
        $this->repo[$key] = $value;
    }

    public function __toString()
    {
        return implode(',', $this->repo);
    }
    
    public function isPkey($b = null)
    {
        if (is_null($b)) {
            return $this->is_pk; 
        } 
        $this->is_pk = $b;
        if ($this->value) {
            $html = $this->html;
            if (empty($_REQUEST[$html])) { 
                $_REQUEST[$html] = $this->value; 
            }
        }
        return $this;
    }

    public function isNullable($v = null)
    {
        if (is_null($v)) { 
            return $this->repo['nullable']; 
        }
        $this->repo['nullable'] = $v;
        return $this;
    }

    public function isUnique($v = null)
    {
        if (is_null($v)) { 
            return $this->repo['unique']; 
        }
        $this->repo['unique'] = $v;
        return $this;
    }
    
    public function setFixLength($length)
    {
        if (!is_array($length)) {
            $length = array($length);
        }
        $this->fixlength = $length;
        return $this;
    }
    
    public function setMaxLength($length)
    {
        $this->maxlength = $length;
        return $this;
    }
    
    public function setMinLenght($length)
    {
        $this->minlength = $length;
        return $this;
    }
    
    public function setValue($value, $default = null)
    {
        if ($value !== '0' && $value !== 0 && empty($value)) {
            $value = $default;
        }
        $this->value = $this->rawvalue = $value;
        $this->defaultValue = $default;
        if ($this->type == 'date' && !empty($value) && strpos($value, '/') !== false) {
            list($dd, $mm, $yy) = explode('/', $this->value );
            $this->value = "$yy-$mm-$dd";            
        }       
        return $this;
    }
    
    public function getValue()
    {        
        return $this->value;                
    }
    
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
    
    public function setUploadPath($path)
    {
        $this->uploadDir = $path;
    }
}
