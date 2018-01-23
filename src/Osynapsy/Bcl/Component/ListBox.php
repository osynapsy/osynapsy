<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Html\Component;
use Osynapsy\Ocl\Component\HiddenBox;
use Osynapsy\Html\Tag;

class ListBox extends Component
{
    public $data = array();
    private $hdn;
    private $box;
    
    public function __construct($id)
    {
        $this->requireJs('/__assets/osynapsy/Bcl/ListBox/script.js');
        $this->requireCss('/__assets/osynapsy/Bcl/ListBox/style.css');
        parent::__construct('div', $id.'_container');
        $this->att('class','listbox');
        $this->hdn = $this->add(new HiddenBox($id));
        $this->box = $this->add(new Tag('div'))
                          ->att('class','listbox-box'); 
    }
    
    protected function __build_extra__()
    {
        $list = $this->add(new Tag('ul'));
        $list->att('class','listbox-list');
        foreach ($this->data as $rec) {
            $selected = '';
            if (array_key_exists($this->hdn->id, $_REQUEST) && ($rec[0] == $_REQUEST[$this->hdn->id])) {
                $this->box->set($rec[1]);
                $selected = ' selected';
            }
            $list->add(new Tag('li'))                
                 ->add(new Tag('div'))
                 ->att('value',$rec[0])
                 ->att('class','listbox-list-item'.$selected)
                 ->add($rec[1]);
        }
    }
    
    public function SetData($data)
    {
        $this->data = $data;
    }
}
