<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Ocl\RadioList as OclRadioList;
use Osynapsy\Html\Ocl\RadioBox as OclRadioBox;
use Osynapsy\Html\Tag;

/**
 * Description of RadioList
 *
 * @author Pietro
 */
class RadioList extends OclRadioList
{
    protected $inline = false;
    
    public function __construct($name, $prefix = null)
    {
        parent::__construct($name, $prefix);
        
    }
    
    protected function __build_extra__()
    {         
        $radioList = $this->add(new Tag('div'));        
        //$dir = $this->getParameter('direction');        
        $radios = [];
        if (!empty($this->prefix)) {
            $radios[] = '<span>'.$this->prefix.'</span>';
        }
        foreach ($this->data as $i => $rec) {
            $radios[] = $this->buildRadio($i+1, $rec);
        }
        $radioList->add(implode($this->inline ? '&nbsp;&nbsp;&nbsp;&nbsp;' : PHP_EOL, $radios));
    }
    
    protected function buildRadio($index, $rec)
    {
        //Workaround for associative array
        list($value, $label) = array_values($rec);       
        //Build radio container;
        $class = ['form-check'];
        if ($this->inline) {
            $class[] = 'form-check-inline';
        }
        $formCheck = new Tag('label', null, implode(' ', $class));
        //Build radio id;
        $radioId = "{$this->id}{$index}";
        //Append radio to container;
        $radio = $formCheck->add(new OclRadioBox($this->id, $radioId));        
        $radio->setClass('form-check-input')->att('value', $value);
        //Append label to container;
        $formCheck->add(new Tag('label', null, 'form-check-label'))->att('for',$radioId)->add($label);
        //Return container;
        return $formCheck;
    }
    
    public function setInLine(bool $inline = true)
    {
        $this->inline = $inline;
    }
}
