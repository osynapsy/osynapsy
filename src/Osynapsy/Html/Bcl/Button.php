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

/**
 * Represents a Html Button.
 *
 * @author Pietro Celeste <p.celeste@osynapsy.org>
 */
class Button extends OclButton
{
    /**
     * Constructor of button component
     * 
     * @param string $id
     * @param string $type button|submit
     * @param string $class extra css class to add to button
     * @param string $label text of the button
     */
    public function __construct($id, $type = 'button', $class = '', $label = '')
    {
        parent::__construct($id);        
        $this->att('type',$type)->att('class','btn '.$class);
        if (!empty($label)) {
            $this->add($label);
        }
    }        
}
