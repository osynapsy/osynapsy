<?php
namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Component;
use Osynapsy\Html\Ocl\HiddenBox;
/**
 * Description of Tags
 *
 * @author pietr
 */
class Tags extends Component
{
    protected $hiddenId;

    public function __construct($id)
    {
        parent::__construct('div', $id.'Box');
        $this->hiddenId = $id;
        $this->setClass('bcl4-tags form-control');
        $this->requireJs('Bcl4/Tags/script.js');
        $this->requireCss('Bcl4/Tags/style.css');
    }

    protected function __build_extra__(): void
    {
        $this->add(new HiddenBox($this->id));
        $this->add('<input type="text" class="bcl4-tags-input" size="1">');
    }
}
