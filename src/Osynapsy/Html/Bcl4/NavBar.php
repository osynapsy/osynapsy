<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Component;
use Osynapsy\Html\Tag;
use Osynapsy\Html\Bcl\Link;

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
        parent::__construct('nav', $id);
        $this->setData([],[]);
        $this->requireCss('Bcl4/NavBar/style.css');
    }
    
    /**
     * Main builder of navbar
     * 
     */
    public function __build_extra__()
    {
        $this->setClass('osy-bcl4-navbar navbar navbar-expand-sm');                
        $this->buildHeader();   
        $collapsable = $this->add(new Tag('div', $this->id.'Content', 'collapse navbar-collapse'));
        $collapsable->add($this->buildUlMenu($this->data['primary'])->att('class','mr-auto', true)); 
        $collapsable->add($this->buildUlMenu($this->data['secondary'])->att('class','float-right', true));
    }
    
    /**
     * Internal method for build header part of navbar
     * 
     * @param type $container
     * @return type
     */
    private function buildHeader()
    {                        
        $brand = $this->getParameter('brand');
        if (!empty($brand)) {
            $this->add(new Tag('a', null, 'navbar-brand'))
                 ->att('href', $brand[1])               
                 ->add($brand[0]);
        }
        $this->add(new Tag('button'))->att([
            'class' => "navbar-toggler",
            'type' => "button",
            'data-toggle' => "collapse",
            'data-target' => "#".$this->id.'Content',
            'aria-controls' => $this->id.'Content',
            'aria-expanded' => "false",
            'aria-label' => "Toggle navigation"
        ])->add('<span class="navbar-toggler-icon fa fa-bars"></span>');
    }
    
    /**
     * Internal method for build a unordered list menù (recursive)
     * 
     * @param object $container of ul
     * @param array $data 
     * @param int $level
     * @return type
     */
    private function buildUlMenu(array $data)
    {
        $ul = new Tag('ul', null, 'navbar-nav');
        if (empty($data) || !is_array($data)) {
            return $ul;
        }               
        foreach($data as $label => $menu){
            $li = $ul->add(new Tag('li', null, 'nav-item'));
            if (is_array($menu)) {                
                $li->att('class','dropdown',true)->add(
                    $this->buildSubMenu($li, $label, $menu)
                );
                continue;
            }            
            $li->add(new Tag('a', null, 'nav-link'))
               ->att('href',$menu)->add($label);            
        }
        return $ul;
    }
    
    private function buildSubMenu($container, $label, array $data, $level = 0) 
    {
        $labelClass = (($level > 0) ? 'dropdown-item' : 'nav-link dropdown-toggle');
        $a = $container->add(new Tag('a', null, $labelClass))->att([
            'href' => '#', 
            'data-toggle' => 'dropdown',                
            'aria-expanded' => 'false',
            'aria-haspopup' => 'true'
        ]);
        $a->add($label);
        $menuClass = 'dropdown-menu';
        if ($level > 0) {
            $menuClass = 'dropdown-submenu d-none';
            $a->att('class','dropdown-item-submenu');
        }
        $menu = $container->add(new Tag('div', null, $menuClass));
        foreach ($data as $label => $link) {
            if (is_array($link)) {
                $this->buildSubMenu($menu, $label, $link, $level + 1);
                continue;
            }elseif ($link === 'hr'){
                $menu->add(new Tag('div', null, 'dropdown-divider'));
                continue;
            }
            $menu->add(new Tag('a', null, 'dropdown-item'))
                 ->att('href', is_array($link) ? '#' : $link)
                 ->add($label);            
        }
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
        $this->att('class','fixed-top',true);
        return $this;
    }
}
