<?php
namespace Osynapsy\Html\Ocl;

//costruttore del text box
class TextBox extends InputBox
{
    public function __construct($nam, $id = null)
    {
        parent::__construct('text', $nam, $this->nvl($id,$nam));
        $this->setParameter('get-request-value',$nam);
    }

    protected function __build_extra__()
    {
        parent::__build_extra__();
        if ($this->getParameter('field-control') == 'is_number'){
            $this->att('type','number')
                 ->att('class','right osy-number',true);
        }
    }
}