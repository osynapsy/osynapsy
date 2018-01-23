<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Html\Tag;
use Osynapsy\Ocl\Component\Component;

class ContextMenu extends Component
{
    private $actions = array();
    private $ul;
    
    public function __construct($id, $link, $label, $class='')
    {
        $this->requireCss('/__assets/osynapsy/Bcl/ContextMenu/style.css');
        $this->requireJs('/__assets/osynapsy/Bcl/ContextMenu/script.js');
        parent::__construct('div', $id);
        $this->att('class', 'BclContextMenu dropdown clearfix');
        $this->ul = $this->add(new Tag('ul'))
                         ->att('class','dropdown-menu')
                         ->att('role','menu')
                         ->att('aria-labelledby','dropdownMenu')
                         ->att('style','display: block; position: static; margin-bottom: 5px;');
        
    }
    
    public function addAction($label, $action, $params='')
    {
        $this->ul
             ->add(new Tag('li'))
             ->add(new Tag('a'))
             ->att('href','javascript:void(0);')
             ->att('data-action',$action)
             ->att('data-action-param',$params)
             ->add($label);
    }
}