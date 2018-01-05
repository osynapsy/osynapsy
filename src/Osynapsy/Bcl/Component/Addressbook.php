<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\Component;
use Osynapsy\Core\Lib\Tag;
use Osynapsy\Bcl\Component\PanelNew;

/**
 * Description of Adressbook
 *
 * @author Peter
 */
class Addressbook extends Component 
{
    protected $columns = 4;
    protected $foot;
    
    public function __construct($id, $columns = 4)
    {
        parent::__construct('dummy', $id.'_dummy');
        $this->columns = $columns;
        $this->requireCss('/__assets/osynapsy/Bcl/Addressbook/style.css');
    }
    
    public function __build_extra__()
    {
        $body = $this->add(new PanelNew($this->id));
        $body->setClass('osy-addressbook','','','');
        if (empty($this->data)) {
            $body->addColumn(12)
                 ->push(
                    false,
                    '<div class="addressbook-empty">'.$this->get_par('title').' &egrave; vuota</div>'
                 );
            return;
        }
        $this->buildBody($body);
        if ($this->foot) {
            $body->addColumn(12)->push(false, $this->foot);
        }
    }
    
    private function buildBody($body)
    {        
        $columnLength = floor(12 / $this->columns);
        foreach($this->data as $i => $rec) {            
            $column = $body->addColumn($columnLength);
            $a = $column->add(new Tag('a'))
                      ->att('href',$rec['href'])
                      ->att('class','osy-addressbook-item');
            $p0 = $a->add(new Tag('div'))->att('class','p0');
            $p1 = $a->add(new Tag('div'))->att('class','p1');
            $p2 = $a->add(new Tag('div'))->att('class','p2');
            $p2->add('&nbsp;');
            foreach($rec as $field => $value) {				
				$this->formatCell($field, $value, $a, $p0, $p1, $p2);
            }
            if (($i+1) % $this->columns === 0) {
                $body->addRow();
            }
        }
    }
    
    private function formatCell($k, $v, $a, $p0, $p1, $p2)
    {
        switch($k) {
            case 'href':
                $a->att('class','save-history',true);
                break;
            case 'class':
                $a->att('class',$v,true);
                break;
            case 'img':
                $p0->add('<img src="'.$v.'">');
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
