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

use Osynapsy\Html\Component as Component;

//Costruttore del pannello html
class Panel extends Component
{
    private $cells = array();
    private $crows = null;
    private $tag = array('tr', 'td');
    private $rowClass = 'row';
    private $cellClass;
    
    public function __construct($id, $tag = 'table', $rowClass = null, $cellClass = null)
    {
        parent::__construct($tag, $id);
        $this->setParameter('label-position', 'outside');
        if (!empty($rowClass)) {
            $this->rowClass = $rowClass;
        }
        if (!empty($cellClass)) {
            $this->cellClass = $cellClass;
        }        
        if ($tag === 'div') {
            $this->tag = array('div','div');
        }
    }
    
    protected function __build_extra__()
    {
        ksort($this->cells);
        
        foreach ($this->cells as $irow => $row){
            ksort($row);
            $this->__row();
            foreach($row as $icol => $col){
                //ksort($col);
                foreach($col as $icnt => $obj) {
                    $colspan=null;
                    if (is_object($obj['obj']) && ($obj['obj']->tag == 'button' || $obj['obj']->getParameter('label-hidden') == '1')) {
                        unset($obj['lbl']);
                        if ($this->getParameter('label-position') == 'outside') $colspan=2;
                    } elseif (!empty($obj['lbl'])) {
                       $label_text = $obj['lbl'];
                       if (is_object($obj['obj'])){
                           if ($prefix = $obj['obj']->getParameter('label-prefix')){
                               $label_text = '<span class="label-prefix">'.$prefix.'</span>'.$label_text;
                           }
                           if ($postfix = $obj['obj']->getParameter('label-postfix')){
                               $label_text .= '<span class="label-postfix">'.$postfix.'</span>';
                           }
                       }
                       //$obj['lbl'] = '<label class="'.(get_class($obj['obj']) == 'panel' ? 'osy-form-panel-label' : 'osy-component-label').'">'.$prefix.$obj['lbl'].'</label>';
                       $obj['lbl'] = new tag('label');
                       $obj['lbl']->att('class',($obj['obj'] instanceof panel ? 'osy-form-panel-label' : 'osy-component-label'))
                                  ->att('class',(is_object($obj['obj']) ? $obj['obj']->getParameter('label-class') : ''),true)
                                  ->add(trim($label_text));
                    }
                    switch ($this->__par['label-position']) {
                        case 'outside':
                            if (key_exists('lbl',$obj)){
                                $cl = $this->cells($obj['lbl']);
                                if (is_object($obj['obj'])){
                                    if ($cls = $obj['obj']->getParameter('label-cell-class')){
                                        $cl->att('class',$cls,true);
                                    }
                                    if ($sty = $obj['obj']->getParameter('label-cell-style')){
                                        $cl->att('style',$sty);
                                    }
                                }
                            }
                            $this->cells($obj['obj'],$colspan);
                            break;
                        case 'outside-rear':
                            $this->cells($obj['obj'],$colspan);
                            if (array_key_exists('lbl',$obj)) {
                                $this->cells($obj['lbl']);
                            }
                            break;
                        default :
                            $this->cells($obj, $colspan);
                            break;
                   }
                }
            }
        }
    }
    
    private function __row()
    {
        return $this->crows = $this->add(tag::create($this->tag[0]))->att('class',$this->rowClass);
    }
    
    private function cells($content = null, $colspan = null)
    {
        if (is_null($content)) {
            return;
        }
        $cel = $this->crows->add(tag::create($this->tag[1]));
        if (!empty($this->cellClass)) {
            $cel->att('class',$this->cellClass);
        }
        if (!empty($colspan)) {
            $cel->att('colspan', $colspan);
        }
        $cel->addFromArray($content);
        return $cel;
    }

    public function put($lbl, $obj, $row = 0, $col = 0)
    {
        $this->cells[$row][$col][] = array('lbl'=>$lbl,'obj'=>$obj);
    }        
}