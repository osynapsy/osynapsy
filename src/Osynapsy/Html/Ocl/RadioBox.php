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

class RadioBox extends InputBox
{
    public function __construct($name, $id)
    {
        parent::__construct('radio', $name, $id);
    }
    
    public function __build_extra__()
    {
        if ($this->value && strpos($this->name,'[')) {
            list($name,) = explode('[',$this->name);
            if (is_array($_REQUEST[$name]) && in_array($this->value, $_REQUEST[$name])) {
                $this->att('checked','checked');
            }
        }
        if (array_key_exists($this->name,$_REQUEST) && $_REQUEST[$this->name] == $this->value){
            $this->att('checked','checked');
        }
    }
}
