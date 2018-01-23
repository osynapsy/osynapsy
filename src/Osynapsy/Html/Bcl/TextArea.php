<?php
namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Ocl\TextArea as OclTextArea2;

class TextArea extends OclTextArea2
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->att('class','form-control',true);        
    }
}
