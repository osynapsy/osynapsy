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

use Osynapsy\Html\Ocl\Button as OclButton;

class Button extends OclButton
{
    
    public function __construct($id, $type = 'button', $class = '', $label = '')
    {
        parent::__construct($id);        
        $this->att('type',$type)->att('class','btn '.$class);
        if (!empty($label)) {
            $this->add($label);
        }
    }
    
    public function setAction($action, $parameters = null)
    {
        $this->att('class','cmd-execute',true)
             ->att('data-action',$action);
        if (!empty($parameters)) {
            $this->att('data-action-parameters',$parameters);
        }
        return $this;
    }    
}
