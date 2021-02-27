<?php
namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Component;
use Osynapsy\Html\Ocl\CheckBox;
use Osynapsy\Html\Tag;

/**
 * Description of Switcher
 *
 * @author pietr
 */
class Switcher extends Component
{
    private $checkBox;
    private $label;

    public function __construct($id, $label)
    {
        parent::__construct('div', $id.'_container');
        $this->setClass('custom-control custom-switch');
        $this->checkBox = $this->add(new CheckBox($id, null, 'dummy'))->getCheckBox();
        $this->checkBox->att('class', 'custom-control-input');
        $this->label = $this->add(new Tag('label', null, "custom-control-label"))->att('for', $id);
        $this->label->add($label);
    }

    public function getCheckBox()
    {
        return $this->checkBox;
    }

    public function setAction($action, $parameters = null, $class = 'click-execute', $confirmMessage = null)
    {
        $this->checkBox->att([
            'data-action' => $action,
            'data-action-parameters' => $parameters
        ]);
        $this->checkBox->att('class', $class, true);
        if (!empty($confirmMessage)) {
            $this->checkBox->att('data-action-confirm', $confirmMessage);
        }
        return $this;
    }
}
