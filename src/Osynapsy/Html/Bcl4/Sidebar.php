<?php
namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Component;

/**
 * Description of Sidebar
 *
 * @author pietr
 */
class Sidebar extends Component
{
    public function __construct($id)
    {
        parent::__construct('div', $id);
        $this->setClass('sidebar');
        $this->requireCss('Bcl4/Sidebar/style.css');
        $this->requireJs('Bcl4/Sidebar/script.js');
        $this->att('data-is-open', '0');
    }
}
