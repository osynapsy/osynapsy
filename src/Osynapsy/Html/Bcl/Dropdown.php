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

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;
use Osynapsy\Html\Ocl\ListUnordered;
use Osynapsy\Html\Ocl\HiddenBox;

class Dropdown extends Component
{
    private $list;
    private $button;
    
    public function __construct($name, $label, $align = 'left', $tag = 'div')
    {
        parent::__construct($tag);
        $this->add(new HiddenBox($name));
        $this->button = $this->att('class','dropdown')
             ->add(new Button($name.'_btn'))
             ->att('type', 'button')
             ->att('class','dropdown-toggle',true)
             ->att('data-toggle','dropdown')
             ->att('aria-haspopup','false')
             ->att('aria-expanded','false');        
        $this->button->add($label.' <span class="caret"></span>');
        $this->list = $this->add(
            new Tag('ul')
        )->att('class','dropdown-menu dropdown-menu-'.$align)
         ->att('onclick',"var t = this; setTimeout(function() { $(t).closest('.dropdown').removeClass('open'); }, 300);")
         ->att('aria-labelledby',$name);
    }
    
    protected function __build_extra__()
    {
        foreach ($this->data as $key => $rec) {
            if (is_object($rec)) {
                $this->list->add(new Tag('li'))->att('data-value',$key)->add($rec);
                continue;
            }
            if ($rec === 'divider') {
                $this->list->add(new Tag('li'))->att(['class' => 'divider','role' => 'separator']);
                continue;
            }
            $rec = array_values($rec);
            $li = $this->list->add(new Tag('li'))->att('data-value',$rec[0]);
            $li->add(is_object($rec[1]) ? $rec[1] : '<a href="#">'.$rec[1].'</a>');
        }
    }
            
    public function getButton()
    {
        return $this->button;
    }    
}
