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

class Dummy extends Component
{
    public function __construct($name, $tag = 'div')
    {
        parent::__construct($tag, $name);
        $this->att('class', 'osy-dummy');
    }
}
