<?php
namespace Osynapsy\Ocl\Component;

use Osynapsy\Html\Component;

class TextArea extends Component
{
    public function __construct($name)
    {
        parent::__construct('textarea',$name);
        $this->name = $name;
    }
    
    public function __build_extra__()
    {
        if (!empty($_REQUEST[$this->id])) {
            $this->add($_REQUEST[$this->id]);
        }
    }
}
