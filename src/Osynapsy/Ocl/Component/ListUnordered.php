<?php
namespace Osynapsy\Ocl\Component;

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;

class ListUnordered extends Component
{
    protected $data = array();
    protected $mainTag;
    protected $itemTag='li';
    
    public function __construct($name, $tag='ul')
    {
        $this->mainTag = $tag;
        parent::__construct($tag, $name);
        if ($this->mainTag == 'div') {
            $this->itemTag = 'a';
        }
    }
    
    protected function __build_extra__()
    {
        foreach ($this->data as $rec) {
            $rec = array_values($rec);
            $this->add(new Tag($this->itemTag))
                 ->att('data-value',$rec[0])
                 ->add($rec[1]);
        }
    }
    
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
    
    public function setHeight($px)
    {
        $this->att('class','overflow-auto border-all',true);
        $this->style = 'height: '.$px.'px;';
        return $this;
    } 
}
