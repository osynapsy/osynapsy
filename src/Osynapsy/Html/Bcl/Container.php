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
    //use Trait for make form command buttons
    use FormCommands;
    
    private $currentRow;
    private $foot;
    private $footLeft;
    private $footRight;
    
    public function __construct($id, $tag = 'div')
    {
        parent::__construct($tag, $id);
        if ($tag == 'form'){
            $this->att('method', 'post');
        }
    }

    private function getFoot($right = false, $offset = 0)
    {
        if (empty($this->foot)) {
            $width = (12 - ($offset * 2)) / 2;
            $lgoffset = empty($offset) ? '' : ' col-lg-offset-'.$offset;
            $this->foot = $this->addRow();
            $this->footLeft = $this->foot->add(new Tag('div', null, 'col-lg-'.$width.$lgoffset));
            $this->footRight = $this->foot->add(new Tag('div', null, 'col-lg-'.$width.' text-right'));
        }
        return empty($right) ? $this->footLeft : $this->footRight;        
    }
    
    public function AddRow()
    {
        return $this->currentRow = $this->add(new Tag('div', null , 'row'));
    }
    
    public function AddColumn($lg = 4, $sm = null, $xs = null)
    {
        $col = new Column($lg);
        $col->setSm($sm);
        $col->setXs($xs);
        if (empty($this->currentRow)) {
            $this->AddRow();
        }
        return $this->currentRow->add($col);
    }
    
    public function setTitle($title)
    {
        $this->AddRow();
        $this->AddColumn(12)->add('<h1>'.$title.'</h1>');
    }
    
    public function setCommand($delete = false, $save = true, $back = true, $offset = 0)
    {
        if ($delete) {
            $this->getFoot(true, $offset)->add($this->getCommandDelete());                 
        }
        if ($save) {
            $this->getFoot(true, $offset)->add($this->getCommandSave($save));
        }        
        if ($back) {
            $this->getFoot()->add($this->getCommandBack());                 
        }
    }
}
