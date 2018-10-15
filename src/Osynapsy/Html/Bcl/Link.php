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

use Osynapsy\Html\Component;

class Link extends Component
{
    public function __construct($id, $link, $label, $class='')
    {
        parent::__construct('a', $id);        
        $this->att('href', $link)
             ->add($label);
        if ($class) {
            $this->att('class', $class);
        }
    }
    
    public function openInModal($title, $widht = '640px', $height = '480px')
    {
        $this->setClass('open-modal');
        $this->att([
            'title' => $title,
            'modal-width' => $widht,
            'modal-height' => $height
        ]);
    }
}
