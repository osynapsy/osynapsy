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

class CheckList extends Component
{
    private $table = null;
    private $groups = [];

    public function __construct($name)
    {
        parent::__construct('div',$name);
        $this->att('class','osy-check-list');
    }

    protected function __build_extra__()
    {
        $this->table =  $this->add(new Tag('table'));
        foreach ($this->data as $value) {
            $this->rowFactory($value);
        }
    }

    protected function rowFactory($value, $level = 0)
    {
        $tr = $this->table->add(new Tag('tr'));
        if (!empty($_REQUEST[$this->id]) && is_array($_REQUEST[$this->id]) && in_array($value[0], $_REQUEST[$this->id])) {
            $value['selected'] = true;
        }
        $tr->add(new Tag('td'))
           ->add(str_repeat('&nbsp;', $level * 7).'<input type="checkbox" class="i-checks checkbox" name="'.$this->id.'[]" value="'.$value[0].'"'.(!empty($value[2]) ? ' checked' : '').'>&nbsp;'.$value[1]);
        if (empty($this->groups[$value[0]])) {
            return;
        }
        foreach($this->groups[$value[0]] as $value) {
            $this->rowFactory($value, $level + 1);
        }
    }

    public function setData($data)
    {
        if (empty($data) || !is_array($data)) {
            return;
        }
        foreach($data as $rec) {
            if (empty($rec['_group'])) {
                $this->data[] = array_values($rec);
            } else {
                $this->groups[$rec['_group']][] = $rec;
            }
        }
    }

    public function setHeight($px)
    {
        $this->style = 'height: '.$px.'px; border: 1px solid black; overflow: auto;';
    }
}
