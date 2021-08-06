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
    protected $tagItem = 'div';
    protected $prefix;

    public function __construct($name, $prefix = null)
    {
        parent::__construct('div', $name);
        $this->att('class','osy-bcl-radio-list');
        $this->prefix = $prefix;
    }

    protected function __build_extra__()
    {
        $list = $this->add(new Tag('div', null, ''));
        //$dir = $this->getParameter('direction');
        if (!empty($this->prefix)) {
            $list->add('<span>'.$this->prefix.'</span>');
        }
        foreach ($this->data as $rec) {
            //Workaround for associative array
            $rec = array_values($rec);
            $list->add($this->itemFactory($rec));
            if ($this->tagItem == 'span') {
                $list->add('&nbsp;&nbsp;&nbsp;&nbsp;');
            }
        }
    }

    protected function itemFactory($rec)
    {
        $item = new Tag($this->tagItem);
        $item->add($this->buildRadio($rec));
        $item->add('&nbsp;'.$rec[1]);
        return $item;
    }

    protected function buildRadio($rec)
    {
        $radio = new RadioBox($this->id, $rec[1] ?? '');
        $radio->att('value',$rec[0]);
        return $radio;
    }
}