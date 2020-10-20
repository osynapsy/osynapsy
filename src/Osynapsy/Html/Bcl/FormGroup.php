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
    protected $info;
    protected $labelClass;

    public function __construct($object, $label = '&nbsp;', $class = 'form-group', $labelClass = 'font-weight-500 text-nowrap')
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
            $this->add($this->labelFactory());
        }
        $this->add($this->object);
        if (!empty($this->info)) {
            $this->add(sprintf('<div>%s</div>', $this->info));
        }
    }

    protected function labelFactory()
    {
        $div = new Tag('div');
        $label = $div->add(new Tag('label', null, $this->labelClass));
        if (is_object($this->object)) {
            $label->att('for',$this->object->id);
        }
        if (!is_array($this->label)) {
            $label->add($this->label);
        } else {
            $label->add($this->label[0]);
        }
        if (is_array($this->label) && array_key_exists(1, $this->label)) {
            $div->add($this->label[1]);
        }
        return $div;
    }

    public function setInfo($info)
    {
        $this->info = $info;
    }
}
