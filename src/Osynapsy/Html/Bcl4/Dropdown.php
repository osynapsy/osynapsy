<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;
use Osynapsy\Html\Ocl\HiddenBox;
use Osynapsy\Html\Bcl\Button;
/**
 * Description of Dropdown
 *
 * @author Pietro
 */
class Dropdown extends Component
{
    private $list;
    private $button;
    private $align;
    public function __construct($name, $label, $align = 'left', $tag = 'div')
    {
        parent::__construct($tag);
        $this->setClass('dropdown');
        $this->add(new HiddenBox($name));
        $this->add($this->buildMainButton($name, $label));
        $this->align = $align;
    }
    
    private function buildMainButton($name, $label)
    {
        $this->button = new Button($name.'_btn', 'button', 'dropdown-toggle', $label);
        $this->button->att([
            'data-toggle' => 'dropdown',
            'aria-haspopup' => 'false',
             'aria-expanded' => 'false'
        ]);
        return $this->button;
    }
    
    protected function __build_extra__()
    {
        $ul = $this->add(new Tag('div', null, 'dropdown-menu dropdown-menu-'.$this->align));        
        $ul->att([            
            'aria-labelledby' => $this->id
        ]);         
        foreach ($this->data as $key => $rec) {
            if (is_object($rec)) {
                $ul->add($rec)->att('class', 'dropdown-item', true);                           
                continue;
            }
            if ($rec === 'divider') {
                $this->list->add(new Tag('li'))->att(['class' => 'divider','role' => 'separator']);
                continue;
            }            
        }
    }
            
    public function getButton()
    {
        return $this->button;
    }    
}
