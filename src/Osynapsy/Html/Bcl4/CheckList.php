<?php
namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Component;
use Osynapsy\Html\Tag;

/**
 * Description of CheckList
 *
 * @author Pietro
 */
class CheckList extends Component
{
    const TYPE_RADIO = 'radio';
    const TYPE_CHECK = 'checkbox';

    protected $action;
    protected $actionParameters;
    protected $class = ['form-check'];
    protected $type;

    public function __construct($id, $type = 'checkbox', array $class = [])
    {
        parent::__construct('dummy', $id);
        $this->type = $type;
        $this->class = array_merge($this->class, $class);
    }

    public function __build_extra__(): void
    {
        foreach ($this->data as $i => $checkProp) {
            $checkId = $this->id.'_'.$i;
            $checked = $this->isChecked($checkProp[0]);
            $this->add($this->buildFormCheck($checkId, $checkProp, $checked));
        }
    }

    protected function isChecked($value)
    {
        $requestValue = $this->type === self::TYPE_RADIO ? [$_REQUEST[$this->id] ?? $this->defaultValue] : $_REQUEST[$this->id] ?? [$this->defaultValue];
        return in_array($value, $requestValue) ? true : false;
    }

    protected function buildFormCheck($checkId, $checkProp, $checked)
    {
        $Container = new Tag('div', null, implode(' ', $this->class));
        $Container->add($this->buildCheck($checkId, $checkProp[0], $checked, $checkProp[2] ?? false));
        $Container->add($this->buildCheckLabel($checkId, $checkProp[1]));
        return $Container;
    }

    protected function buildCheck($checkId, $value, $checked, $disabled)
    {
        $Check = new Tag('input', $checkId, 'form-check-input no-mask');
        $Check->att(array_filter([
            'name' => $this->type === 'radio' ? $this->id : sprintf('%s[]',$this->id),
            'type' => $this->type,
            'id' => $checkId,
            'value' => $value,
            'checked' => !empty($checked) ? 'checked' : null,
            'disabled' => !empty($disabled) ? 'disabled' : null,
            'data-action' => $this->action,
            'data-action-parameters' => $this->actionParameters
        ]));
        if (!empty($this->action)) {
            $Check->addClass('click-execute');
        }
        return $Check;
    }

    protected function buildCheckLabel($checkId, $labelText)
    {
        $label = new Tag('label', null, 'form-check-label');
        $label->att('for', $checkId);
        $label->add($labelText);
        return $label;
    }

    public function setAction($action, $parameters = null, $class = 'click-execute', $confirmMessage = null)
    {
        $this->action = $action;
        $this->actionParameters = $parameters;
        return $this;
    }

    public function setInline()
    {
        $this->class[] = 'form-check-inline';
    }
}
