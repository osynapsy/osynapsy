<?php
namespace Osynapsy\Ocl\Component;

use Osynapsy\Html\Tag as Tag;

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
        if (!$this->currentValue) {
            $this->currentValue = $this->defaultValue;
        }
        if (!empty($this->data) && $this->isTree && array_key_exists(2,$this->data[0])) {
            if (!$this->get_par('option-select-disable')){
                array_unshift($this->data, array('','- select -','_group'=>''));                
            }
            $this->buildTree($this->data);
            return;
        } 
        if (!$this->get_par('option-select-disable')){ 
            if ($lbl = $this->get_par('label-inside')){
                $this->placeholder = $lbl;
            }
            array_unshift($this->data, array('', $this->placeholder)); 
        }                     
        foreach ($this->data as $k => $item) {
            $this->addOption($item[0], $item[1]);            
        }
    }
    
    public function addOption($optionValue, $label)
    {               
        $option = $this->add(new Tag('option'))->att('value', $optionValue);
        $option->add($this->nvl($label, $optionValue));
        if ($this->currentValue == $optionValue) {
            $option->att('selected','selected');
        }
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
        $len = count($dat) - 1;        
        foreach ($dat as $k => $rec) {
            $val = array();
            foreach ($rec as $j => $v) {
                if (!is_numeric($j)) {
                    continue;
                } elseif (count($val) == 2) {
                    continue;
                }               
                $val[] = empty($val) ? $v : str_repeat('&nbsp;',$lev*5).$v;
            }           
            $opt = $this->add(new Tag)
                        ->att('value',$val[0]);
            $opt->add($this->nvl($val[1],$val[0]));
            if ($this->currentValue == $val[0]) {
                $opt->att('selected','selected');
            }
            //$this->add('<option value="'.$val[0].'"'.$sel.'>'.nvl($val[1],$val[0])."</option>\n");
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
}
