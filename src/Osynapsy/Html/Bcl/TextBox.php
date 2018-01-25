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

use Osynapsy\Html\Ocl\TextBox as OclTextBox;

class TextBox extends OclTextBox
{
    public function __construct($name, $class='')
    {
        parent::__construct($name);
        $this->att('class',trim('form-control '.$class),true);
    }
}
