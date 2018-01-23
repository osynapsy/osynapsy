<?php
namespace Osynapsy\Ocl\Component;

//Field hidden
class HiddenBox extends InputBox
{
    public function __construct($name, $id = null)
    {
        parent::__construct('hidden', $name, $this->nvl($id, $name));
    }
}
