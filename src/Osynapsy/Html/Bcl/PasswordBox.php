<?php
namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Ocl\PasswordBox as OclPasswordBox;

class PasswordBox extends OclPasswordBox
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->att('class','form-control',true);
    }
}
