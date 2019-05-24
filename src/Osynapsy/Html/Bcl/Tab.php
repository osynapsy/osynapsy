<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;
use Osynapsy\Html\Ocl\HiddenBox;
use Osynapsy\Html\Bcl\Panel;

class Tab extends Component
{
    private $ul;
    private $nCard=0;
    private $currentCard;
    private $tabContent;
    private $tabSelected;
    private $id;
    
    public function __construct($id)
    {
        parent::__construct('dummy');
        $this->id = $id;
        $this->requireJs('Bcl/Tab/script.js');
        $this->add(new HiddenBox($id));
        $this->ul = $this->add(new Tag('ul'));
        $this->ul->att([
            'id' => $id.'_nav', 
            'class' => 'nav nav-tabs mt-1',
            'role' => 'tablist',
            'data-tabs' => 'tabs'
        ]);
        $this->tabSelected = empty($_REQUEST[$this->id]) ? '1' : $_REQUEST[$this->id];
        $this->tabContent = $this->add(new Tag('div', null, 'tab-content p-2'));
    }
    
    public function addCard($title)
    {
        $cardId = $this->id.'_'.$this->nCard++;
        $li = $this->ul->add(new Tag('li', null, 'nav-item'))->att('role','presentation');        
        if($this->tabSelected == $this->nCard) {
            $li->att('class', 'active in', true);
        }        
        $li->add('<a href="#'.$cardId.'" data-toggle="tab" class="nav-link">'.$title.'</a>');
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
