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

use Osynapsy\Html\Tag;

class Container extends Tag
{
    private $currentRow;
    
    public function __construct($id, $tag='div')
    {
        parent::__construct($tag, $id);
        if ($tag == 'form'){
            $this->att('method','post');
        }
    }

    public function AddRow()
    {
        return $this->currentRow = $this->add(new Tag('div'))->att('class','row');
    }
    
    public function AddColumn($lg = 4, $sm = null, $xs = null)
    {
        $col = new Column($lg);
        $col->setSm($sm);
        $col->setXs($xs);
        if ($this->currentRow) {
            return $this->currentRow->add($col);
        }
        return $this->add($col);
    }
}
