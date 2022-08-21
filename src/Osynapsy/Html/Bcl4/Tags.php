<?php
namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Component;
use Osynapsy\Html\Tag;
use Osynapsy\Html\Ocl\HiddenBox;
/**
 * Description of Tags
 *
 * @author pietr
 */
class Tags extends Component
{
    protected $hiddenId;
    private $labelClass;

    public function __construct($id, $labelClass = 'badge badge-info')
    {
        parent::__construct('div', $id.'Box');
        $this->hiddenId = $id;
        $this->labelClass = $labelClass;
        $this->setClass('bcl4-tags form-control');
        $this->requireJs('assets/Bcl4/Tags/script.js');
        $this->requireCss('assets/Bcl4/Tags/style.css');
    }

    protected function __build_extra__(): void
    {
        $this->add(new HiddenBox($this->hiddenId));
        if (!empty($_REQUEST[$this->hiddenId])) {
            $this->add($this->tagsFactory($_REQUEST[$this->hiddenId]));
        }
        $textbox = $this->add(new Tag('input', null, 'bcl4-tags-input'))->att(['type' => 'text', 'size' => '1']);
        if (!empty($this->data)) {
            $textbox->att('list', $this->hiddenId.'Datalist');
            $this->add($this->datalistFactory($this->data));
        }
    }

    protected function tagsFactory($strTags)
    {
        $result = new Tag('dummy');
        $tags = explode('][', $strTags);
        foreach($tags as $tag) {
            $result->add($this->tagFactory($tag));
        }
        return $result;
    }

    protected function tagFactory($tag)
    {
        $value = str_replace(['[',']'], '', $tag);
        $wrapper = new Tag('h5', null, 'd-inline mr-1');
        $wrapper->add(sprintf('<input type="hidden" name="__%s[]" value="%s">', $this->hiddenId, $value));
        $badge = $wrapper->add(new Tag('span', null, $this->labelClass));
        $badge->add($value);
        $badge->add(new Tag('span', null, 'fa fa-times bcl4-tags-delete'));
        return $wrapper;
    }

    protected function datalistFactory($rawOptions)
    {
        $datalist = new Tag('datalist', $this->hiddenId.'Datalist', 'bcl4-tags-datalist');
        foreach ($rawOptions as $rawOption) {
            $option = array_values($rawOption);
            $datalist->add(new Tag('option'))->add($option[0]);
        }
        return $datalist;
    }
}
