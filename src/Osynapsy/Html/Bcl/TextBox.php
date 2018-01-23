<?php
namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Ocl\TextBox as OclTextBox;

class TextBox extends OclTextBox
{
    public function __construct($name, $class='')
    {
        parent::__construct($name);
        $this->att('class',trim('form-control '.$class),true);
    }
}
