<?php
namespace Osynapsy\Html\Ocl;

use Osynapsy\Html\Component as Component;

class InputBox extends Component
{
    public function __construct($type, $name, $id = null)
    {
        parent::__construct('input', $id);
        $this->att('type', $type)
             ->att('name', $name);
    }

    protected function __build_extra__()
    {
        if ($value = $this->getGlobal($this->name, $_REQUEST)) {
            $this->att('value', $value);
        }
    }
}
