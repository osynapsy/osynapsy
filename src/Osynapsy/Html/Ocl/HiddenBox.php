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

//Field hidden
class HiddenBox extends InputBox
{
    public function __construct($name, $id = null)
    {
        parent::__construct('hidden', $name, $this->nvl($id, $name));
    }
}
