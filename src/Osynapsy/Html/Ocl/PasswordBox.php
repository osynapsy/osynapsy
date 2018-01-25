<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Ocl;

class PasswordBox extends InputBox
{
    public function __construct($name, $id = null)
    {
        parent::__construct('password', $name, parent::nvl($id, $name));
        $this->att('autocomplete','off');
    }
}
