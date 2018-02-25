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
    private $foot;
    private $footLeft;
    private $footRight;
    
    public function __construct($id, $tag='div')
    {
        parent::__construct($tag, $id);
        if ($tag == 'form'){
            $this->att('method','post');
        }
    }

    private function getFoot($right = false)
    {
        if (empty($this->foot)) {
            $this->foot = $this->addRow();
            $this->footLeft = $this->foot->add(new Tag('div'))->att('class', 'col-lg-6');
            $this->footRight = $this->foot->add(new Tag('div'))->att('class', 'col-lg-6 text-right');
        }
        return empty($right) ? $this->footLeft : $this->footRight;        
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
    
    public function setTitle($title)
    {
        $this->AddRow();
        $this->AddColumn(12)->add('<h1>'.$title.'</h1>');
    }
    
    public function setCommand($delete = false, $save = true, $back = true)
    {
        if ($delete) {
            $this->getFoot(true)
                 ->add(new Button('btn_delete', 'button', 'btn-danger'))
                 ->setAction('delete')
                 ->att('data-confirm', 'Sei sicuro di voler eliminare il record corrente?')
                 ->add('<span class="glyphicon glyphicon-trash"></span> Elimina');
        }
        if ($save) {
            $this->getFoot(true)
                 ->add(new Button('btn_save', 'button', 'btn-primary'))
                 ->setAction('save')
                 ->add($save === true ? '<span class="glyphicon glyphicon-floppy-disk"></span> Salva' : $save);
        }
        
        if ($back) {
            $this->getFoot()
                 ->add(new Button('btn_back'))
                 ->att('class','cmd-back btn btn-default')
                 ->add('<span class="glyphicon glyphicon-chevron-left"></span> Indietro');
        }
    }
}
