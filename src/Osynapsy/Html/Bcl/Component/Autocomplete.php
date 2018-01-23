<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Html\Component;
use Osynapsy\Html\Ocl\Component\HiddenBox;
use Osynapsy\Html\Tag;

class Autocomplete extends Component
{
    private $emptyMessage;
    private $ico = '<span class="fa fa-search"></span>';
    
    public function __construct($id)
    {
        $this->requireJs('/__assets/osynapsy/Bcl/Autocomplete/script.js');
        $this->requireCss('/__assets/osynapsy/Bcl/Autocomplete/style.css');
        parent::__construct('div', $id);
    }
    
    public function __build_extra__()
    {
        if (filter_input(\INPUT_POST, 'ajax') != $this->id) {            
            $this->addInput();
            return;
        }
        $this->addValueList();
    }
    
    private function addInput()
    {
         $this->add(new InputGroup($this->id, '', $this->ico))
              ->att('class','osy-autocomplete',true)
              ->add(new HiddenBox('__'.$this->id));
    }
    
    private function addValueList()
    {        
        $valueList = $this->add(new Tag('div'));
        $valueList->att('id',$this->id.'_list');
        if (empty($this->data) || !is_array($this->data)) {
            $valueList->add('<div class="row">'.$this->emptyMessage.'</div>');
            return;
        }
        foreach ($this->data as $rec) {
            $val = array_values($rec);
            if (empty($val) || empty($val[0])) {
                continue;
            }
            switch (count($val)) {               
                case 1:                
                    $val[1] = $val[2] = $val[0];
                    break;
                case 2:
                    $val[2] = $val[1];
                    break;
            }
            $src    = filter_input(\INPUT_POST,$this->id);
            $val[2] = str_replace($src,'<b>'.$src.'</b>',$val[2]);
            $valueList->add('<div class="row" data-value="'.$val[0].'" data-label="'.$val[1].'">'.$val[2].'</div>'.PHP_EOL);
        }
    }
    
    public function setLabel($label)
    {
        $_REQUEST[$this->id] = $label;
        return $this;
    }
    
    public function setEmptyMessage($msg)
    {
        $this->emptyMessage = $msg;
        return $this;
    }
    
    public function setSelected($function)
    {
        $this->onselected = $function;        
        return $this;
    }
    
    public function setUnSelected($function)
    {
        $this->onunselected = $function;   
        return $this;
    }
    
    public function setIco($ico)
    {
        $this->ico = $ico;
    }
}
