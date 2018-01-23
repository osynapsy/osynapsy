<?php
namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Component as Component;

class Nav extends Component
{
    public function __construct($id)
    {
        parent::__construct('div',$id.'_tab');
        $this->add(new HiddenBox($id));
    }
}
