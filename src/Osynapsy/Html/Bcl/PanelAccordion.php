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

use Osynapsy\Html\Component as Component;

//Costruttore del pannello html
class PanelAccordion extends Component
{
    private $panels = array();
    
    public function __construct($id)
    {
        parent::__construct('div', $id);
        $this->att('class','panel-group')
             ->att('role','tablist');
    }
    
    public function __build_extra__()
    {
        foreach($this->panels as $panel) {
            $this->add($panel);
        }
    }
    
    public function addPanel($title)
    {
        $panelIdx = count($this->panels);
        $panelId = $this->id.$panelIdx;
        $panelTitle = '<a data-toggle="collapse" data-parent="#'.$this->id.'" href="#'.$panelId.'-body" class="'.(empty($panelIdx) ? 'collapsed' : '').'">'.$title.'</a>';
        $this->panels[] = new PanelNew($panelId, $panelTitle);
        $this->panels[$panelIdx]
             ->getBody()
             ->att('id', $panelId.'-body');
        $this->panels[$panelIdx]->setClass('panel-body collapse' .(empty($panelIdx) ? ' in' : ''));             
        return $this->panels[$panelIdx];
    }
}
