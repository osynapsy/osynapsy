<?php
namespace Osynapsy\Ocl\Component;

use Osynapsy\Html\Component;

class CheckBox extends Component
{
    private $hidden = null;
    private $checkbox = null;
    
    public function __construct($name)
    {
        parent::__construct('span',$name);
        $this->hidden = $this->add('<input type="hidden" name="'.$name.'" value="0">');
        $this->checkbox = $this->add(new InputBox('checkbox',$name,$name));
        $this->checkbox->att('class','osy-check')->att('value','1');
    }
    
    protected function __build_extra__()
    {
        if (array_key_exists($this->id,$_REQUEST) && !empty($_REQUEST[$this->id])) {
            $this->checkbox->att('checked','checked');
        }
    }
    
    public function getCheckbox()
    {
        return $this->checkbox;
    }
}
