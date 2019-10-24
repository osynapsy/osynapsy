<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Component;
use Osynapsy\Html\Tag;

class FormGroup extends Component
{
    public $label;
    public $object;
    private $labelClass;
    
    public function __construct($object, $label = '&nbsp;', $class = 'form-group', $labelClass = 'font-weight-500')
    {
        parent::__construct('div');
        $this->att('class', $class);
        $this->label = $label;
        $this->labelClass = $labelClass;
        $this->object = $object;
    }
    
    public function __build_extra__()
    {
        if (!empty($this->label)) {
            $label = $this->add(new Tag('div'))->add(new Tag('label', null, $this->labelClass));
            $label->add($this->label);
            if (is_object($this->object)) {
                $label->att('for',$this->object->id);
            }
        }
        $this->add($this->object);
    }
}
