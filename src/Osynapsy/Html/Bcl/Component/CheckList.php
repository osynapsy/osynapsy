<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Html\Ocl\Component\CheckList as OclCheckList;

class CheckList extends OclCheckList
{
    public function __construct($name)
    {
        parent::__construct($name);        
        $this->requireCss('/__assets/osynapsy/Bcl/CheckList/style.css');
    }
}
