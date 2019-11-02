<?php
namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Bcl\Form as Bcl3_Form;

/**
 * Description of Form
 *
 * @author Pietro
 */
class Form extends Bcl3_Form
{
    public function __construct($name, $mainComponent = 'Panel', $tag = 'form')
    {
        parent::__construct($name, $mainComponent, $tag);
        if ($mainComponent === 'Panel') {
            $this->body->setClasses('card', 'card-header', 'card-body', 'card-footer');
        }
    }
    
    public function resetClass()
    {
        $this->body->setClasses('', '', '', '');
    }
}
