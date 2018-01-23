<?php
namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Ocl\ComboBox as OclComboBox;

class ComboBox extends OclComboBox
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->att('class','form-control',true);
    }
}
