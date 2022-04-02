<?php
namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;
use Osynapsy\Html\Bcl\Button;

/**
 * Description of Dropdown
 *
 * @author Pietro
 */
class Dropdown extends Component
{
    const ALIGN_LEFT = 'left';
    const ALIGN_RIGHT = 'right';

    private $button;
    private $align;

    public function __construct($name, $label, $align = self::ALIGN_LEFT, $tag = 'div')
    {
        parent::__construct($tag);
        $this->setClass('dropdown');
        $this->add($this->buildMainButton($name, $label));
        $this->align = $align;
    }

    private function buildMainButton($name, $label)
    {
        $this->button = new Button($name.'_btn', 'button', 'btn dropdown-toggle', $label);
        $this->button->att([
            'data-toggle' => 'dropdown',
            'aria-haspopup' => 'true',
            'aria-expanded' => 'false'
        ]);
        return $this->button;
    }

    protected function __build_extra__()
    {
        $list = $this->add(new Tag('div', null, 'dropdown-menu dropdown-menu-'.$this->align));
        $list->att('aria-labelledby', $this->getButton()->id);
        foreach ($this->data as $rec) {
            if (is_object($rec)) {
                $list->add($rec)->att('class', 'dropdown-item', true);
                continue;
            }
            if ($rec === 'divider') {
                $list->add(new Tag('div', null, 'dropdown-divider'));
                continue;
            }
        }
    }

    public function getButton()
    {
        return $this->button;
    }
}
