<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Html\Tag;
use Osynapsy\Ocl\Component\Component;
use Osynapsy\Ocl\Component\ListUnordered;
use Osynapsy\Ocl\Component\HiddenBox;

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
             ->att('aria-haspopup','true')
             ->att('aria-expanded','false');        
        $this->button->add($label.' <span class="caret"></span>');
        $this->list = $this->add(
            new Tag('ul')
        )->att('class','dropdown-menu dropdown-menu-'.$align)
         ->att('aria-labelledby',$name);

    }
    
    protected function __build_extra__()
    {
        foreach ($this->data as $key => $rec) {
            if (is_object($rec)) {
                $this->list->att('data-value',$key)->add(new Tag('li'))->add($rec);
                continue;
            }
            $rec = array_values($rec);
            $this->list
                 ->add(new Tag('li'))
                 ->att('data-value',$rec[0])
                 ->add('<a href="#">'.$rec[1].'</a>');
        }
    }
            
    public function getButton()
    {
        return $this->button;
    }
}
