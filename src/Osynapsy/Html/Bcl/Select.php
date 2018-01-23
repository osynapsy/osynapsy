<?php
namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Ocl\ComboBox2;

/**
 * Description of Select
 *
 * @author Peter
 */
class Select extends ComboBox2
{
    //put your code here
    public function __construct($name, $multiple=false)
    {        
        parent::__construct($name);
        $this->class = 'osy-select';
        $this->requireCss('/__assets/osynapsy/Lib/bootstrap-select-1.10.0/bootstrap-select.css');
        $this->requireJs('/__assets/osynapsy/Lib/bootstrap-select-1.10.0/bootstrap-select.js');
        $this->requireJs('/__assets/osynapsy/Bcl/Select/script.js');
        //$this->par('option-select-disable',false);
        if ($multiple) {
            $this->setMultiSelect();
        }
    }
    
    public function setMultiSelect()
    {
        $this->att('multiple','multiple');
        if (strpos($this->name,'[') === false) {
            $this->name = $this->name.'[]';
        }
        return $this;
    }
}
