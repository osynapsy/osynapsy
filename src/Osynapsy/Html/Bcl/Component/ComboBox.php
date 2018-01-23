<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Html\Ocl\Component\ComboBox as OclComboBox;

class ComboBox extends OclComboBox
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->att('class','form-control',true);
    }
}
