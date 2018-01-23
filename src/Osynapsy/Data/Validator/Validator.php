<?php
namespace Osynapsy\Data\Validator;

abstract class Validator
{    
    protected $field = array();
    
    public function __construct(&$field) 
    {
        $this->field = $field;
        if (!array_key_exists('label', $this->field)) {
            $this->field['label'] = $this->field['name'];
        }
    }
    
    abstract public function check();
}

