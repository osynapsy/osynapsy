<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\Component;

class Column extends Component
{
    private $size = array(
        'lg' => array('width' => null, 'offset' => 0),
        'md' => array('width' => null, 'offset' => 0),
        'sm' => array('width' => null, 'offset' => 0),
        'xs' => array('width' => null, 'offset' => 0)
    );

    public function __construct($size = 2, $offset = 0)
    {
        parent::__construct('div');
        $this->setLg($size, $offset);
        $this->setMd($size, $offset);
        $this->setSm($size, $offset);
        $this->setXs(0, 0);
    }
    
    protected function __build_extra__()
    {
        foreach ($this->size as $size => $dimension) {
            if (empty($dimension['width'])) {
                continue;
            }
            $class = 'col-'.$size.'-'.$dimension['width'];
            if (!empty($dimension['offset'])) {
                $class .= ' col-'.$size.'-offset-'.$dimension['offset'];
            }
            $this->att('class', $class, true);
        }
    }
    
    public function setLg($size, $offset = 0)
    {
        $this->size['lg']['width'] = $size;
        $this->size['lg']['offset'] = $offset;
         return $this;
    }
    
    public function setMd($size, $offset = 0)
    {
        $this->size['md']['width'] = $size;
        $this->size['md']['offset'] = $offset;
         return $this;
    }
    
    public function setSm($size, $offset = 0)
    {
        $this->size['sm']['width'] = $size;
        $this->size['sm']['offset'] = $offset;
         return $this;
    }
    
    public function setXs($size, $offset = 0)
    {
        $this->size['xs']['width'] = $size;
        $this->size['xs']['offset'] = $offset;
        return $this;
    }
    
    public function push($label, $object, $grouped=true)
    {
        if ($object instanceof Tag) {
            $object->att('data-label', $label);
        }
        if ($grouped) {
            $this->add(new FormGroup($object,$label));
        } else {
            $this->add($object);
        }
        return $this;
    }    
}
