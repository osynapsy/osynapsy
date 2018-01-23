<?php
namespace Osynapsy\Ocl\Component;

use Osynapsy\Html\Tag as Tag;
use Osynapsy\Ocl\Component\Component as Component;

//Costruttore del pannello html
class Panel extends Component
{
    private $cells = array();
    private $crows = null;
    private $tag = array('tr','td');
    private $rowClass = 'row';
    private $cellClass;
    
    public function __construct($id, $tag = 'table', $rowClass = null, $cellClass = null)
    {
        parent::__construct($tag,$id);
        $this->par('label-position','outside');
        if (!empty($rowClass)) {
            $this->rowClass = $rowClass;
        }
        if (!empty($cellClass)) {
            $this->cellClass = $cellClass;
        }
        
        if ($tag=='div') $this->tag = array('div','div');
    }
    
    protected function __build_extra__()
    {
        ksort($this->cells);
        
        foreach($this->cells as $irow => $row)
        {
            ksort($row);
            $this->__row();
            foreach($row as $icol => $col)
            {
                //ksort($col);
                foreach($col as $icnt => $obj)
                {
                    $colspan=null;
                    if (is_object($obj['obj']) && ($obj['obj']->tag == 'button' || $obj['obj']->get_par('label-hidden') == '1')) {
                        unset($obj['lbl']);
                        if ($this->get_par('label-position') == 'outside') $colspan=2;
                    } elseif (!empty($obj['lbl'])) {
                       $label_text = $obj['lbl'];
                       if (is_object($obj['obj'])){
                           if ($prefix = $obj['obj']->get_par('label-prefix')){
                               $label_text = '<span class="label-prefix">'.$prefix.'</span>'.$label_text;
                           }
                           if ($postfix = $obj['obj']->get_par('label-postfix')){
                               $label_text .= '<span class="label-postfix">'.$postfix.'</span>';
                           }
                       }
                       //$obj['lbl'] = '<label class="'.(get_class($obj['obj']) == 'panel' ? 'osy-form-panel-label' : 'osy-component-label').'">'.$prefix.$obj['lbl'].'</label>';
                       $obj['lbl'] = new tag('label');
                       $obj['lbl']->att('class',($obj['obj'] instanceof panel ? 'osy-form-panel-label' : 'osy-component-label'))
                                  ->att('class',(is_object($obj['obj']) ? $obj['obj']->get_par('label-class') : ''),true)
                                  ->add(trim($label_text));
                    }
                    switch ($this->__par['label-position']) {
                        case 'outside':
                            if (key_exists('lbl',$obj)){
                                $cl = $this->cells($obj['lbl']);
                                if (is_object($obj['obj'])){
                                    if ($cls = $obj['obj']->get_par('label-cell-class')){
                                        $cl->att('class',$cls,true);
                                    }
                                    if ($sty = $obj['obj']->get_par('label-cell-style')){
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
    
    private function cells($content=null,$colspan=null)
    {
        if (is_null($content)) return;
        $cel = $this->crows->add(tag::create($this->tag[1]));
        if (!empty($this->cellClass)) {
            $cel->att('class',$this->cellClass);
        }
        if (!empty($colspan)) $cel->att('colspan',$colspan);
        $cel->add2($content);
        return $cel;
    }

    public function put($lbl, $obj, $row = 0, $col = 0)
    {
        $this->cells[$row][$col][] = array('lbl'=>$lbl,'obj'=>$obj);
    }
    
    public function buildPdf($pdf, $xwidth=190, $xstart=10)
    {
        //Scorro le righe;
        ksort($this->cells);
        foreach ($this->cells as $k => $row){
            if (!is_array($row)) continue;
            ksort($row);
            $ncel = count($row);
            $wcel = $xwidth / $ncel;
            $cury = $pdf->GetY();
            $h = 0;
            foreach($row as $i => $cels)
            {
                foreach($cels as $j => $obj)
                {
                    $pdf->SetXY($wcel * $h+$xstart,$cury);
                    $pdf->setFont('helvetica','B',10);
                    if (is_object($obj['obj']) && method_exists($obj['obj'],'build_pdf')){
                        $pdf->SetFillColor(230,230,230);
                        $pdf->Cell($wcel,7,strtoupper($obj['lbl']),($ncel == ($h+1) ? 'LTR' : 'LT'),0,'C',1);
                    } else {
                        $pdf->Cell($wcel,7,$obj['lbl'],($ncel == ($h+1) ? 'LTR' : 'LT'),0,'L',0);
                    }
                    $pdf->SetFillColor(0);
                    $pdf->setFont('helvetica','',12);
                    $pdf->Ln();
                    if (is_object($obj['obj']) && method_exists($obj['obj'],'build_pdf')){
                        $obj['obj']->build_pdf($pdf,$wcel,$wcel*$h+$xstart);
                        continue;
                    }
                    $val = '';
                    if (is_object($obj['obj']) && method_exists($obj['obj'],'get_value'))
                    {
                        $val = $obj['obj']->get_value();
                        $wdt = $pdf->GetStringWidth($val);
                        if ($wcel < $wdt){
                            $min = floor(($wcel / $wdt) * strlen($val));
                            $val = substr($val,0,$min);
                        }
                    }
                    $pdf->SetX($wcel * $h + $xstart);
                    $pdf->Cell($wcel,7,$val,($ncel == ($h+1) ? 'LRB' : 'LB'));
                    $pdf->Ln();
                }
                $h++;
            }
        }
    }
}