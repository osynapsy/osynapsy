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

/**
 * Build a Bootstrap NavBar
 *
 */
class NavBar2 extends Component
{
    /**
     * Constructor require dom id of component
     *
     * @param string $id
     */
    public function __construct($id, $class = null)
    {
        parent::__construct('nav', $id);
        $this->setData([],[]);
        $this->requireCss('Bcl4/NavBar/style.css');
        $this->requireJs('Bcl4/NavBar/script.js');
        if (!empty($class)) {
            $this->setClass($class);
        }
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
        if (empty($this->data)) {
            return;
        }
        if (!empty($this->data['primary'])) {
            $collapsable->add($this->buildUlMenu($this->data['primary'])->addClass('mr-auto'));
        }
        if (!empty($this->data['secondary'])) {
            $ul = $this->buildUlMenu($this->data['secondary'], 0 ,'dropdown-menu dropdown-menu-lg-right');
            $ul->addClass('justify-content-end');
            $collapsable->add($ul);
        }
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
        $brandPrefix = $this->getParameter('brandPrefix');
        if (!empty($brandPrefix)) {
            $this->add($brandPrefix);
        }
        if (!empty($brand)) {
            $this->add(new Tag('a', null, 'navbar-brand'))
                 ->att('href', $brand[1])
                 ->add($brand[0]);
        }
        $this->add(new Tag('button', null, 'navbar-toggler'))->att([
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
     * @param array $data
     * @param int $level
     * @param string $dropdownMenuClass class to apply drowdownMenu
     * @return type
     */
    private function buildUlMenu(array $data, $level = 0, $dropdownMenuClass = 'dropdown-menu')
    {
        //Add ul menù container;
        $ul = new Tag('ul', null, empty($level) ? 'navbar-nav' : $dropdownMenuClass);
        if (empty($data) || !is_array($data)) {
            return $ul;
        }
        foreach($data as $label => $menu){
            $li = $ul->add(new Tag('li', null, 'nav-item'));
            $li->att('role', 'navigation');
            if ($menu === 'hr'){
                $li->add($this->getNavDivider());
                continue;
            }
            if (!is_array($menu)) {
                $li->add($this->getNavLink($label, $menu, $level));
                continue;
            }
            $li->add($this->getNavDropdownLink($label, $level));
            $li->addClass(empty($level) ? 'dropdown' : 'dropdown-submenu')->add(
                $this->buildUlMenu($menu, $level + 1, $dropdownMenuClass)
            );
        }
        return $ul;
    }

    private function getNavDropdownLink($label, $level)
    {
        $a = new Tag('a', null, 'dropdown-toggle '.(empty($level) ? 'nav-link' : 'dropdown-item'));
        $a->att([
            'href' => '#',
            'data-toggle' => 'dropdown',
            'aria-expanded' => 'false',
            'aria-haspopup' => 'true'
        ])->add($label);
        return $a;
    }

    private function getNavLink($label, $url, $level)
    {
        $a = new Tag('a', null, empty($level) ? 'nav-link' : 'dropdown-item');
        $a->att('href', $url)->add($label);
        return $a;
    }

    private function getNavDivider()
    {
        return new Tag('div', null, 'dropdown-divider');
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
    public function setBrand($label, $href = '#', $prefix = null)
    {
        $this->setParameter('brand', [$label, $href]);
        $this->setParameter('brandPrefix', $prefix);
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
