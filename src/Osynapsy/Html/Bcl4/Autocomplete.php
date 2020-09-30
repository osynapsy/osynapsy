<?php
namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Bcl\Autocomplete as AutocompleteBcl3;
use Osynapsy\Html\Bcl4\InputGroup;

/**
 * Description of AutoComplete
 *
 * @author Pietro
 */
class AutoComplete extends AutocompleteBcl3
{
    protected $ico = '<span class="fa fa-search"></span>';

    public function __construct($id, $db = null)
    {
        parent::__construct($id, $db);
    }

    protected function buildAutocomplete()
    {
        $autocomplete = new InputGroup($this->id, '', $this->ico);
        $autocomplete->getTextBox()->onselect = 'event.stopPropagation();';
        return $autocomplete->setClass('osy-autocomplete');
    }
}
