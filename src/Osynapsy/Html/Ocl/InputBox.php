<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Ocl;

use Osynapsy\Html\Component as Component;

class InputBox extends Component
{
    protected $defaultValue;
    
    public function __construct($type, $name, $id = null)
    {
        parent::__construct('input', $id);
        $this->att('type', $type)
             ->att('name', $name);
    }

    protected function __build_extra__()
    {
        $value = $this->getGlobal($this->name, $_REQUEST);
        $this->att('value', (empty($value) && $value != '0' ? $this->defaultValue : $value));
    }
    
    public function setDefaultValue($value)
    {
        $this->defaultValue = $value;
    }
}
