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

use Osynapsy\Html\Ocl\Link as OclLink;

class Link extends OclLink
{    
    public function openInModal($title, $widht = '640px', $height = '480px')
    {
        $this->setClass('open-modal')->att([
            'title' => $title,
            'modal-width' => $widht,
            'modal-height' => $height
        ]);
    }
}
