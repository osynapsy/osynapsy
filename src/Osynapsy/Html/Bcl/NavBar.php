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
        $this->setData([],[]);
    }
    
    /**
     * Main builder of navbar
     * 
     */
    public function __build_extra__()
    {
        $this->setClass('navbar navbar-default');
        $container = $this->add(new Tag('div'));
        $container->att('class', $this->getParameter('containerClass'));
        
        $this->buildHeader($container);
        $collapse = $container->add(new Tag('div',$this->id.'_collapse'))->att('class','collapse navbar-collapse');
        $this->buildUlMenu($collapse, $this->data['primary'])->att('class','nav navbar-nav'); 
        $this->buildUlMenu($collapse, $this->data['secondary'])->att('class','nav navbar-nav pull-right');
    }
    
    /**
     * Internal method for build header part of navbar
     * 
     * @param type $container
     * @return type
     */
    private function buildHeader($container)
    {                
        $header = $container->add(new Tag('div'))
                  ->att('class','navbar-header');
        $header->add(
            '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#'.$this->id.'_collapse" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>'
        );
        $brand = $this->getParameter('brand');
        if (empty($brand)) {
            return;
        }
        $header->add(new Tag('a'))
               ->att('href', $brand[1])
               ->att('class','navbar-brand')
               ->add($brand[0]);
    }
    
    /**
     * Internal method for build a unordered list menù (recursive)
     * 
     * @param object $container of ul
     * @param array $data 
     * @param int $level
     * @return type
     */
    private function buildUlMenu($container, array $data, $level = 0)
    {
        $ul = $container->add(new Tag('ul'))
                        ->att('class', ($level > 0 ? 'dropdown-menu' : ''));
        if (empty($data) || !is_array($data)) {
            return $ul;
        }               
        foreach($data as $label => $menu){
            $li = $ul->add(new Tag('li'));
            if (!is_array($menu)) {
                $li->add(new Tag('a'))->att('href',$menu)->add($label);                
                continue;
            }
            $li->att('class','dropdown')
                ->add(new Tag('a'))
                ->att(['class' => 'dropdown-toggle', 'href' => '#', 'data-toggle' => 'dropdown'])
                ->add($label.' <span class="caret"></span>');
            $this->buildUlMenu($li, $menu, $level + 1);
        }
        return $ul;
    }
    
    /**
     * Decide if use fluid (true) or static container (false)
     * 
     * @param type $bool 
     * @return $this
     */
    public function setContainerFluid($bool = true)
    {
        $this->setParameter('containerClass','container'.($bool ? '-fluid' : ''));
        return $this;
    }
    
    /**
     * Set brand identity (logo, promo etc) to start menù    
     * 
     * @param string $label is visual part of brand
     * @param string $href is url where user will be send if click brand
     * @return $this
     */
    public function setBrand($label, $href = '#')
    {
        $this->setParameter('brand', [$label, $href]);
        return $this;
    }
    
    /**
     * Set data necessary for build NavBar.     
     * 
     * @param array $primary set main menu data (near brand) 
     * @param array $secondary set second menù aligned to right
     * @return $this Navbar component
     */
    public function setDataMenu(array $primary, array $secondary = [])
    {
        $this->data['primary'] = $primary;
        $this->data['secondary'] = $secondary;
        return $this;
    }
    
    /**
     * Fix navigation bar on the top of page (navbar-fixed-top class on main div)
     * 
     * @return $this
     */
    public function setFixedOnTop()
    {
        $this->att('class','navbar-fixed-top',true);
        return $this;
    }
}
