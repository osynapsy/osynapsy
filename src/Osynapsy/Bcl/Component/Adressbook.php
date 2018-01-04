<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\Component;
use Osynapsy\Core\Lib\Tag;

/**
 * Description of Adressbook
 *
 * @author Peter
 */
class Adressbook extends Component 
{
    
    public function __construct($id,$pag=0)
    {
        parent::__construct('div', $id);
        $this->att('class','osy-addressbook')
             ->att('pag',$this->__par['pag_cur']);
        $this->__par['title']       = '';
        $this->__par['row_shw']     = 16;
        $this->__par['ajax']        = false;
        $this->__par['scroll-master'] = 'self';
		env::$page->add_script('/lib/osy-2.1/js/osy.addressbook.js');
    }
    
    public function __build_extra__()
    {
        $this->buildBody();
    }
    
    private function buildBody()
    {
		//$rs = $this->__db->execquery($this->__sql);
        $body = new tag('div');
        $body->att('class','osy-addressbook-body')
             ->att('pag',$this->get_par('pag_cur'))
             ->att('scroll-master',$this->get_par('scroll-master'));
        if (empty($this->data)) {
            $this->add($bbody)
                 ->add('<div class="addressbook-empty">'.$this->get_par('title').' &egrave; vuota</div>');
            return;
        }
        foreach($this->data as $k => $rec) {
           $a = $b->add(new Tag('div'))->att('class','osy-addressbook-item');
           $p0 = $a->add(new Tag('div'))->att('class','p0');
           $p1 = $a->add(new Tag('div'))->att('class','p1');
           $p2 = $a->add(new Tag('div'))->att('class','p2');
           $p2->add('&nbsp;');
           $href = null;
           $i = 0;
           foreach($rec as $k => $v) {				
				$this->formatCell($k, $v, $a, $p1, $p2, $p3, $i, $href);
           }
        }
        $this->add($b);
        $this->add('<br id="qnn-end" style="clear: both">');
    }
    
    private function formatCell($k, $v, $a, $p1, $p2, $p3, &$i, &$href)
    {
        switch($k) {
            case '_class':
                $a->att('class',$v,true);
                break;
            case '_href':
                $href = $v;
                break;
            case '_p0':
                $img = '<img src="'.$v.'">';
                if (!empty($href)) {
                    $img = '<a href="'.$href.'">'.$img.'</a>';
                }
                $p0->add($img);
                break;
            case '_p2':
                $p2->add('<span>'.$v.'</span><br>');
                break;
            default:
                $i++;
                if (empty($v)) {
                    break;
                }
                if (empty($href)) {
                    $p1->add('<span class="s'.$i.'">'.$v.'</span><br>');
                } else {
                    $p1->add('<a class="s'.$i.'" href="'.$href.'">'.$v.'</a><br>');
                }
                break;
        }
    }
    
    public function cmd_upd($cmd,$lbl='Modifica')
    {
        $this->par('cmd_upd',$cmd);
        if ($this->par('cmd_add')) {
            $this->par('cmd_add','<a href="#" onclick="'.$cmd.'"><lbl></a>');
        }
        $this->par('cmd_upd_lbl',$lbl);
    }
}
