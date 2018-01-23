<?php
namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Component;
use Osynapsy\Html\Ocl\HiddenBox;

class Alert extends Component
{
    protected $hiddenBox;
    
    public function __construct($id, $value, $type='info')
    {
        parent::__construct('div', $id.'_label');
        $hiddenBox = $this->add(new HiddenBox($id));
        $this->att('class','alert alert-'.$type)
             ->att('role','alert')
             ->add($value);
    }
}