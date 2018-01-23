<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Html\Ocl\Component\PasswordBox as OclPasswordBox;

class PasswordBox extends OclPasswordBox
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->att('class','form-control',true);
    }
}
