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
/*
 * Button component
 */
class Button extends Component
{
    public function __construct($nam, $id = null, $typ = 'button')
    {
        parent::__construct('button', $this->nvl($id,$nam));
        $this->att('name',$nam);
        $this->att('type',$typ);
        $this->att('label',null);
    }
    
    protected function __build_extra__()
    {
        if ($label = $this->getParameter('label')) {
            $this->add('<span>'.$label.'</span>');
        }
    }
}
