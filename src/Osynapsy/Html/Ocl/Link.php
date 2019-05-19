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

use Osynapsy\Html\Component;

class Link extends Component
{
    public function __construct($id, $link, $label, $class = '')
    {
        parent::__construct('a', $id);        
        $this->att('href', $link)->add($label);        
        $this->SetClass($class);
    }        
}
