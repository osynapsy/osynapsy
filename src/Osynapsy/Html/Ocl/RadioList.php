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

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;

class RadioList extends Component
{
    protected $tagItem;
    protected $prefix;
    
    public function __construct($name, $prefix = null)
    {
        parent::__construct('div', $name);
        $this->att('class','osy-bcl-radio-list');
        $this->prefix = $prefix;
    }

    protected function __build_extra__()
    {
        $table = $this->add(new Tag('div', null, ''));
        //$dir = $this->getParameter('direction');
        if (!empty($this->prefix)) {
            $table->add('<span>'.$this->prefix.'</span>');
        }
        foreach ($this->data as $rec) {
            $this->buildRadio($rec);
            //Workaround for associative array
            $rec = array_values($rec);
            $tr = $table->add(new Tag($this->tagItem));
            $radio = $tr->add(new RadioBox($this->id));
            $radio->att('value',$rec[0]);
            $tr->add('&nbsp;'.$rec[1]);
            if ($this->tagItem == 'span') {
                $tr->add('&nbsp;&nbsp;&nbsp;&nbsp;');
            }
        }
    }
}