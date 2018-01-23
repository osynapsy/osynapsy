<?php
namespace Osynapsy\Html\Ocl\Component;

//costruttore del text box
class TextBox extends InputBox
{
    public function __construct($nam, $id = null)
    {
        parent::__construct('text', $nam, $this->nvl($id,$nam));
        $this->par('get-request-value',$nam);
    }

    protected function __build_extra__()
    {
        parent::__build_extra__();
        if ($this->get_par('field-control') == 'is_number'){
            $this->att('type','number')
                 ->att('class','right osy-number',true);
        }
    }
}