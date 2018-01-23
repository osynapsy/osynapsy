<?php
namespace Osynapsy\Ocl\Component;

class PasswordBox extends InputBox
{
    public function __construct($name, $id = null)
    {
        parent::__construct('password', $name, parent::nvl($id, $name));
        $this->att('autocomplete','off');
    }
}
