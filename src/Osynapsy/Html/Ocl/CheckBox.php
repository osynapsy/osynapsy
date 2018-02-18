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

use Osynapsy\Html\Component;
use Osynapsy\Html\Tag;

class CheckBox extends Component
{    
    private $checkbox = null;
    
    public function __construct($name)
    {
        parent::__construct('span',$name);
        $this->add('<input type="hidden" name="'.$name.'" value="0">');
        $this->checkbox = $this->add(new Tag('input'))->att([
            'id' => $name,
            'type' => 'checkbox',
            'name' => $name,
            'value' => '1'
        ]);
        $this->checkbox->att('class','osy-check')->att('value','1');
    }
    
    protected function __build_extra__()
    {
        if (!empty($_REQUEST[$this->id])) {
            $this->checkbox->att('checked','checked');
        }
    }
    
    public function getCheckbox()
    {
        return $this->checkbox;
    }
}
