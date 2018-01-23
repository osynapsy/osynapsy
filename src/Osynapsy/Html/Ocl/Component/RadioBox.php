<?php
namespace Osynapsy\Html\Ocl\Component;

class RadioBox extends InputBox
{
    public function __construct($name)
    {
        parent::__construct('radio',$name);
    }
    
    public function __build_extra__()
    {
        if ($this->value && strpos($this->name,'[')) {
            list($name,) = explode('[',$this->name);
            if (is_array($_REQUEST[$name]) && in_array($this->value, $_REQUEST[$name])) {
                $this->att('checked','checked');
            }
        }
        if (array_key_exists($this->name,$_REQUEST) && $_REQUEST[$this->name] == $this->value){
            $this->att('checked','checked');
        }
    }
}
