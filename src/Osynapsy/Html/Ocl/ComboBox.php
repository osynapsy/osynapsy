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

use Osynapsy\Html\Tag as Tag;
use Osynapsy\Html\Component;

class ComboBox extends Component
{
    public $isTree = false;
    public $placeholder = ['', '- Seleziona -'];
    protected $defaultValue;
    protected $currentValue;

    public function __construct($name)
    {
        parent::__construct('select', $name);
        $this->att('name', $name);
    }

    protected function __build_extra__()
    {
        $this->currentValue = $this->getGlobal($this->name, $_REQUEST);
        if (empty($this->currentValue) && $this->currentValue != '0') {
            $this->currentValue = $this->defaultValue;
        }
        if (!empty($this->placeholder) && !$this->getParameter('option-select-disable')){
            array_unshift($this->data, $this->placeholder);
        }
        foreach ($this->data as $item) {
            $item = array_values(!is_array($item) ? [trim($item)] : $item);
            $value = $item[0];
            $label = isset($item[1]) ? $item[1] : $item[0];
            $disabled = empty($item[2]) ? false : true;
            $this->optionFactory($value, $label, $disabled);
        }
    }

    protected function optionFactory($value, $label, $disabled = 0)
    {
        $option = (new Tag('option'))->att('value', $value);
        $option->add($this->nvl($label, $value));
        if ($disabled) {
            $this->att('disabled','disabled');
        }
        if ($this->currentValue == $value) {
            $option->att('selected', 'selected');
        }
        if (empty($this->getAttribute('readonly')) || !empty($option->getAttribute('selected'))) {
            $this->add($option);
        }
        return $option;
    }

    public function countOption()
    {
        return count($this->data);
    }

    public function setAction($action, $parameters = null, $class = 'change-execute', $confirmMessage = null)
    {
        return parent::setAction($action, $parameters, $class, $confirmMessage);
    }

    public function setArray($array)
    {
        $this->data = $array;
        return $this;
    }

    public function setDefaultValue($value)
    {
        $this->defaultValue = $value;
        return $this;
    }

    public function setPlaceholder($label, $value = '')
    {
        $this->placeholder = $label === false ? [] : [$value, $label];
        return $this;
    }
}
