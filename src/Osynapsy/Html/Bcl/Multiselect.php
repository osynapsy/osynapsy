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

use Osynapsy\Html\Ocl\ComboBox;
/**
 * Description of Multiselect
 *
 * @author p.celeste@osynapsy.org
 */
class Multiselect extends ComboBox
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->requireCss('Lib/boostrap-multiselect-2.0/bootstrap-multiselect.css');
        $this->requireJs('Lib/boostrap-multiselect-2.0/bootstrap-multiselect.js');
        $this->requireJs('Bcl/Multiselect/script.js');        
        $this->setClass('osy-multiselect')->att('multiple','multiple');
        $this->setParameter('option-select-disable',true);
    }
}
