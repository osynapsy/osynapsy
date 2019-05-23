<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Component;
use Osynapsy\Html\Ocl\HiddenBox;
use Osynapsy\Html\Tag;

/**
 * Description of Carousel
 *
 * @author pietr
 */
class Carousel extends Component
{
    private $showCommands; 
    private $showIndicators;
    
    public function __construct($id, $showCommands = false, $showIndicators = false)
    {
        parent::__construct('div', $id);
        $this->showCommands = $showCommands;
        $this->showIndicators = $showIndicators;
    }
    
    protected function __build_extra__()
    {
        if (!empty($this->data)) {
            return;
        }
        if ($this->showIndicators) {
            $this->buildIndicators();
        }
        $inner = $this->add(new Tag('div', null, 'carousel-inner'));
        foreach($this->data as $key => $rec) {
            $item = $inner->add($this->buildItem($rec));
            if (empty($key)) {
                $item->att('class', 'active', true);
            }
        }
        if ($this->showCommands) {
            $this->buildCommands($inner);
        }
    }
    
    private function buildItem($rec)
    {
        $div = new Tag('div', null, 'carousel-item');
        $div->add(new Tag('img', null, 'd-block w-100'))->att(array_pop($rec));
        $this->buildItemCaption($rec, $div);
        return $div;
    }
    
    private function buildItemCaption($rec, $item)
    {
        if (empty($rec)) {
            return;
        }
        $caption = $item->add(new Tag('div', null, 'carousel-caption d-none d-md-block'));
        $texts = array_values($rec);
        if (empty($texts[0])) {
            $caption->add(new Tag('h5'))->add($text[0]);
        }
        if (empty($texts[1])) {
            $caption->add(new Tag('p'))->add($text[1]);
        }
    }
    
    private function buildCommands($inner)
    {
        foreach(['prev','next'] as $cmd) {
            $a = $inner->add(new Tag('a', null, 'carousel-control-'.$cmd));
            $a->att([
                'href' => "#carouselExampleControls", 
                'role'=> "button", 
                'data-slide' => "prev"
            ]);
            $a->add(new Tag('span', null, "carousel-control-{$cmd}-icon"))
              ->att('aria-hidden', "true");
            $a->add(new Tag('span', null, 'sr-only'))->add($cmd);
        }
    }
    
    private function buildIndicators()
    {
        $ol = $this->add(new Tag('ol', null, 'carousel-indicators'));
        foreach(array_keys($ol) as $key) {
            $li = $ol->add(new Tag('li', null, empty($key) ? 'active' : null));
            $li->att(['data-target' => "#{$this->id}", "data-slide-to" => $key]);
        }
    }
}
