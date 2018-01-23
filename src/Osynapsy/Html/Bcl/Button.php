<?php
namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Ocl\Button as OclButton;

class Button extends OclButton
{
    
    public function __construct($id, $type = 'button', $class = '', $label = '')
    {
        parent::__construct($id);        
        $this->att('type',$type)->att('class','btn '.$class);
        if (!empty($label)) {
            $this->add($label);
        }
    }
    
    public function setAction($action, $parameters = null)
    {
        $this->att('class','cmd-execute',true)
             ->att('data-action',$action);
        if (!empty($parameters)) {
            $this->att('data-action-parameters',$parameters);
        }
        return $this;
    }    
}
