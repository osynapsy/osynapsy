<?php
namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Component;
use Osynapsy\Html\Tag;

class FormGroup extends Component
{
    public $label;
    public $object;

    public function __construct($object, $label = '&nbsp;')
    {
        parent::__construct('div');
        $this->att('class','form-group');
        $this->label = $label;
        $this->object = $object;
    }
    
    public function __build_extra__()
    {
        if (!empty($this->label)) {
            $label = $this->add(new Tag('div'))->add(new Tag('label'));
            $label->add($this->label);
            if (is_object($this->object)) {
                $label->att('for',$this->object->id);
            }
        }
        $this->add($this->object);
    }
}
