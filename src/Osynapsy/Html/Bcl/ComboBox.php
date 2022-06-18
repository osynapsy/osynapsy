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

use Osynapsy\Html\Ocl\ComboBox as OclComboBox;

class ComboBox extends OclComboBox
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->setClass('form-control');
    }

    public function enableSearch()
    {
        $this->addClass('selectpicker');
        $this->att('data-live-search', 'true');
        $this->requireCss('Bcl/ComboBox/bootstrap-select.css');
        $this->requireJs('Bcl/ComboBox/bootstrap-select.js');
    }
    
    public function setSmallSize()
    {
        $this->setClass('form-control-sm');
        return $this;
    }
}
