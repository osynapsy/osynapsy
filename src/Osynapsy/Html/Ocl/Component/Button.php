<?php
namespace Osynapsy\Html\Ocl\Component;

use Osynapsy\Html\Component;
/*
 * Button component
 */
class Button extends Component
{
    public function __construct($nam, $id = null, $typ = 'button')
    {
        parent::__construct('button', $this->nvl($id,$nam));
        $this->att('name',$nam);
        $this->att('type',$typ);
        $this->att('label',null);
    }
    
    protected function __build_extra__()
    {
        if ($label = $this->get_par('label')) {
            $this->add('<span>'.$label.'</span>');
        }
    }
}
