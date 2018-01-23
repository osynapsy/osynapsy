<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Html\Tag;
use Osynapsy\Ocl\Component\Component;
use Osynapsy\Ocl\Component\HiddenBox;
use Osynapsy\Bcl\Component\Panel;

class Tab extends Component
{
    private $ul;
    private $nCard=0;
    private $currentCard;
    private $tabContent;
    private $id;
    
    public function __construct($id)
    {
        parent::__construct('dummy');
        $this->id = $id;
        $this->requireJs('/__assets/osynapsy/Bcl/Tab/script.js');
        $this->add(new HiddenBox($id));
        $this->ul = $this->add(new Tag('ul'));
        $this->ul->att([
            'id' => $id.'_nav', 
            'class' => 'nav nav-tabs',
            'role' => 'tablist',
            'data-tabs' => 'tabs'
        ]);
        $this->tabContent = $this->add(new Tag('div'))->att('class','tab-content');
    }
    
    public function addCard($title)
    {
        $cardId = $this->id.'_'.$this->nCard++;
        $li = $this->ul->add(new Tag('li'))->att('role','presentation');
        if ($this->nCard == 1) {
            //$li->att('class','active');
        }
        $li->add('<a href="#'.$cardId.'" data-toggle="tab">'.$title.'</a>');
        $this->currentCard = $this->tabContent->add(new Panel($cardId))->att('class' , 'tab-pane fade no-border', true);
        return $this->currentCard;
    }
    
    public function put($label, $object, $col, $row, $width = 1, $colspan = null, $class = '')
    {
        $this->currentCard->put($label, $object, $col, $row, $width, $colspan, $class);
    }
    
    public function setType($type)
    {
        $this->currentCard->setType($type);
    }
}
