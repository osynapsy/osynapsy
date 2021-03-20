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
use Osynapsy\Html\Bcl\Alert;

class Container extends Tag
{
    //use Trait for make form command buttons
    use FormCommands;

    private $alert;
    private $alertCount = 0;
    private $currentRow;
    private $foot;
    protected $footClass;
    private $footLeft;
    private $footRight;

    public function __construct($id, $tag = 'div')
    {
        parent::__construct($tag, $id);
        if ($tag == 'form'){
            $this->att('method', 'post');
        }
    }

    public function alert($label, $type = 'danger')
    {
        if (empty($this->alert)) {
            $this->alert = $this->add(new Tag('div'));
            $this->alert->att('class','transition animated fadeIn m-b-sm');
        }
        $icon = '';
        switch ($type) {
            case 'danger':
                $icon = '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span>';
                break;
        }
        $alert = new Alert('al'.$this->alertCount, $icon.' '.$label, $type);
        $alert->att('class','alert-dismissible text-center',true)
              ->add(' <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>');
        $this->alert->add($alert);
        $this->alertCount++;
        return $this->alert;
    }

    public function getFoot($right = false)
    {
        if (empty($this->foot)) {
            $this->foot = $this->add(new Tag('div', null, 'px-1 '.$this->footClass));
            $this->foot->style = 'background-color: rgba(255,255,255,0.8); border-top: 1px solid #ddd;';
            $this->footLeft = $this->foot->add(new Tag('div', null, 'float-left'));
            $this->footRight = $this->foot->add(new Tag('div', null, 'float-right'));
            $this->foot->add('<div class="clearfix"></div>');
        }
        return empty($right) ? $this->footLeft : $this->footRight;
    }

    public function AddRow($class = '')
    {
        return $this->currentRow = $this->add(new Tag('div', null , trim('row '.$class)));
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
            $this->getFoot(true, $offset)->add($this->buttonDeleteFactory());
        }
        if ($save) {
            $this->getFoot(true, $offset)->add($this->buttonSaveFactory($save));
        }
        if ($back) {
            $this->getFoot()->add($this->buttonBackFactory());
        }
    }

    public function fixCommandBar($class = 'fixed-bottom py-2 b-light')
    {
       $this->footClass = $class;
    }
}
