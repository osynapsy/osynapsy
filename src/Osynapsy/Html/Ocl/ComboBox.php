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

//costruttore del combo box
class ComboBox extends Component
{    
    public $__grp = array();
    public $isTree = false;
    public $placeholder = '- Seleziona -';
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
        if (!empty($this->data) && $this->isTree && array_key_exists(2,$this->data[0])) {
            if (!$this->getParameter('option-select-disable')){
                array_unshift($this->data, array('','- select -','_group'=>''));                
            }
            $this->buildTree($this->data);
            return;
        } 
        if (!$this->getParameter('option-select-disable')){ 
            if ($lbl = $this->getParameter('label-inside')){
                $this->placeholder = $lbl;
            }
            array_unshift($this->data, array('', $this->placeholder)); 
        }                     
        foreach ($this->data as $k => $item) {
            if (!is_array($item)) {
                continue;
            }
            $item = array_values($item);
            $this->addOption($item[0], (isset($item[1]) ? $item[1] : $item[0]));            
        }
    }
    
    public function addOption($value, $label)
    {               
        $option = $this->add(new Tag('option'))->att('value', $value);
        $option->add($this->nvl($label, $value));        
        if ($this->currentValue == $value) {
            $option->att('selected', 'selected');
        }
        return $option;
    }
    
    private function buildTree($res)
    {
        $dat = array();
        foreach ($res as $k => $rec) {
            if (empty($rec[2])) {
                $dat[] = $rec;
            } else {
                $this->__grp[$rec[2]][] = $rec;
            }
        }
        $this->buildBranch($dat);
    }

    private function buildBranch($dat, $lev = 0)
    {
        if (empty($dat)) {
            return;
        }        
        foreach ($dat as $k => $rec) {
            $val = array();
            foreach ($rec as $j => $v) {
                if (!is_numeric($j) || count($val) === 2) {
                    continue;
                }
                $val[] = empty($val) ? $v : str_repeat('&nbsp;',$lev*5).$v;
            }            
            $this->addOption($val[0], $val[1]);
            if (array_key_exists($val[0],$this->__grp)) {
                $this->buildBranch($this->__grp[$val[0]],$lev+1);
            }
        }
    }
    
    public function setArray($array)
    {
        $this->data = $array;
        return $this;
    }

    public function setTree($active = true)
    {
        $this->isTree = $active;
    }
    
    public function setDefaultValue($value)
    {
        $this->defaultValue = $value;
        return $this;
    }
    
    public function countOption()
    {
        return count($this->data);
    }
    
    public function getOptionData($key = null)
    {
        if (is_null($key)) {
            return $this->data;
        } elseif (!array_key_exists($key, $this->data)) {
            return [];
        }
        return $this->data[$key];
    }
}
