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

use Osynapsy\Html\Component as Component;

class Nav extends Component
{
    public function __construct($id)
    {
        parent::__construct('div',$id.'_tab');
        $this->add(new HiddenBox($id));
    }
}
