<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Html\Ocl\Component\TextArea as OclTextArea2;

class TextArea extends OclTextArea2
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->att('class','form-control',true);        
    }
}
