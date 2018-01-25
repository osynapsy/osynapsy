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

use Osynapsy\Html\Component as OclComponent;
use Osynapsy\Html\Tag;

/**
 * Build a card
 * 
 */
class Card extends OclComponent
{
    public function __construct($name, $title=null)
    {
        parent::__construct('div',$name);
        $this->att('class','card');
        if (!empty($title)) {
            $this->add(new Tag('div'))
                 ->att('class','card-header ch-alt')
                 ->add('<h2>'.$title.'</h2>');
        }
    }
}
