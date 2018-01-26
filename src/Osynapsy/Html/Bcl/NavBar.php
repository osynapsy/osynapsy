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

/**
 * Build a Bootstrap NavBar
 * 
 */
class NavBar extends Component
{        
    /**
     * Constructor require dom id of component
     * 
     * @param string $id
     */
    public function __construct($id)
    {
        parent::__construct('nav', $id.'_navbar');
        $this->setParameter('containerClass', 'container');        
    }
    
    public function __build_extra__()
    {
        $this->setClass('navbar navbar-default');
        $container = $this->add(new Tag('div'));
        $container->att('class', $this->getParameter('containerClass'));
        
        $this->buildBrand($container);
        $this->buildUlMenu($container, $this->data);
    }
    
    private function buildBrand($container)
    {
        $brand = $this->getParameter('brand');
        if (empty($brand)) {
            return;
        }
        $container->add(new Tag('div'))
                  ->att('class','navbar-header')
                  ->add(new Tag('a'))
                  ->att('href', $brand[1])
                  ->add($brand[0]);
    }
    
    private function buildUlMenu($container, $data, $level = 0)
    {
        if (empty($data) || !is_array($data)) {
            return;
        }
        $ul = $container->add(new Tag('ul'));
        if ($level > 0) {
            $ul->att('class', 'dropdown-menu');
        }
        foreach($data as $label => $menu){
            $li = $ul->add(new Tag('li'));
            if (!is_array($menu)) {
                $li->add(new Tag('a'))->att('href',$menu)->add($label);                
                continue;
            }
            $li->att('class','dropdown')
                ->add(new Tag('a'))
                ->att(['class' => 'dropdown-toggle', 'href' => '#'])
                ->add($label.' <span class="caret"></span>');
            $this->buildUlMenu($li, $menu, $level + 1);
        }
    }
    
    public function setContainerFluid($bool = true)
    {
        $this->setParameter('containerClass','container'.($bool ? '-fluid' : ''));
    }
    
    public function setBrand($label, $href)
    {
        $this->setParameter('brand', [$label, $href]);
    }
}
