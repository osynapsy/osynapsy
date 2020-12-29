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
        $this->requireJs('Bcl4/Tags/script.js');
        $this->requireCss('Bcl4/Tags/style.css');
    }

    protected function __build_extra__(): void
    {
        $this->add(new HiddenBox($this->hiddenId));
        if (!empty($_REQUEST[$this->hiddenId])) {
            $this->add($this->tagsFactory($_REQUEST[$this->hiddenId]));
        }
        $this->add('<input type="text" class="bcl4-tags-input" size="1">');
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
        $wrapper = new Tag('h5', null, 'd-inline mr-1');
        $badge = $wrapper->add(new Tag('span', null, $this->labelClass));
        $badge->add(str_replace(['[',']'], '', $tag));
        $badge->add(new Tag('span', null, 'fa fa-close bcl4-tags-delete'));
        return $wrapper;
    }
}
