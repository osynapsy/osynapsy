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
use Osynapsy\Html\Bcl\PanelNew;

/**
 * Description of Adressbook
 *
 * @author Peter
 */
class Addressbook extends PanelNew 
{
    protected $columns = 4;
    protected $foot;
    protected $emptyMessage;
    protected $itemSelected;
    
    public function __construct($id, $emptyMessage = 'Addressbook is empty', $columns = 4)
    {
        parent::__construct($id);
        $this->setClass('','','','osy-addressbook');
        $this->columns = $columns;
        $this->emptyMessage = $emptyMessage;
        $this->requireCss('Bcl/Addressbook/style.css');
        $this->requireJs('Bcl/Addressbook/script.js');
    }
    
    protected function __build_extra__()
    {
        $this->itemSelected = empty($_REQUEST[$this->id.'_chk']) ? [] : $_REQUEST[$this->id.'_chk'];
        
        if (empty($this->data)) {
            $this->addColumn(12)
                 ->push(false,'<div class="osy-addressbook-empty"><span>'.$this->emptyMessage.'</span></div>');
            return;
        }
        $this->buildBody();
        if ($this->foot) {
            $this->addColumn(12)->push(false, $this->foot);
        }
        parent::__build_extra__();
    }
    
    private function buildBody()
    {        
        $columnLength = floor(12 / $this->columns);
        foreach($this->data as $i => $rec) {            
            $column = $this->addColumn($columnLength);
            $a = $column->add(new Tag('div'))
                        ->att('class','osy-addressbook-item');            
            $p0 = $a->add(new Tag('div'))->att('class','p0');
            $p1 = $a->add(new Tag('div'))->att('class','p1');
            $p2 = $a->add(new Tag('div'))->att('class','p2');
            $p2->add('&nbsp;');
            foreach($rec as $field => $value) {				
				$this->formatCell($field, $value, $a, $p0, $p1, $p2);
            }
            if (($i+1) % $this->columns === 0) {
                $this->addRow();
            }
        }
    }
    
    private function formatCell($k, $v, $a, $p0, $p1, $p2)
    {
        if ($k[0] === '_') {
            return;
        }
        switch($k) {
            case 'checkbox':
                $checked = '';
                if (!empty($this->itemSelected[$v])) {
                    $a->att('class','osy-addressbook-item-selected',true);                    
                    $checked=' checked="checked"';
                }
                $a->add('<span class="fa fa-check"></span>');
                $a->add('<input type="checkbox" name="'.$this->id.'_chk['.$v.']" value="'.$v.'"'.$checked.' class="osy-addressbook-checkbox">');
                break;
            case 'href':
                $a->add(new Tag('a'))
                  ->att('href',$v)
                  ->att('class','osy-addressbook-link save-history fa fa-pencil');
                break;
            case 'class':
                $a->att('class',$v,true);
                break;
            case 'img':
                if (!empty($v)) {
                    $v = '<img src="'.$v.'" class="osy-addressbook-img">';
                } else {
                    $v = '<span class="fa fa-user-o fa-2x osy-addressbook-img text-center" style="padding-top: 3px"></span>';
                }
                $p0->add($v);
                break;
            case 'tag':
                $p2->add('<span>'.$v.'</span><br>');
                break;
            case 'title':
                $v = '<strong>'.$v.'</strong>';
            default:
                $p1->add('<div class="p1-row">'.$v.'</div>');
                break;
        }
    }
    
    public function addToFoot($content)
    {
        if (!$this->foot) {
            $this->foot = new Tag('div');
            $this->foot->class = 'text-center';
        }
        $this->foot->add($content);
    }
}
