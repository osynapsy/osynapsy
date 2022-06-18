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

use Osynapsy\Html\Ocl\ComboBoxTree as OclComboBoxTree;

class ComboBoxTree extends OclComboBoxTree
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->setClass('form-control');
    }
}
