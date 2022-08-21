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
use Osynapsy\Html\Ocl\HiddenBox;

//Costruttore del pannello html
class PanelAccordion extends Component
{
    private $panels = array();

    public function __construct($id)
    {
        parent::__construct('div', $id);
        $this->att('class','panel-group osy-panel-accordion')
             ->att('role','tablist');
        $this->requireCss('assets/Bcl/PanelAccordion/style.css');
        $this->requireJs('assets/Bcl/PanelAccordion/script.js');
    }

    public function __build_extra__()
    {
        $this->add(new HiddenBox($this->id));
        foreach($this->panels as $panel) {
            $this->add($panel);
        }
    }

    public function addPanel($title, $commands = [])
    {
        $panelIdx = count($this->panels);
        $panelId = $this->id.$panelIdx;
        $panelTitle = '<a data-toggle="collapse" data-parent="#'.$this->id.'" href="#'.$panelId.'-body" data-panel-id="'.$panelIdx.'" class="'.(filter_input(\INPUT_POST, $this->id) == $panelIdx ? 'collapsed' : '').'" onclick="">'.$title.'</a>';
        $this->panels[] = new PanelNew($panelId, $panelTitle);
        $this->panels[$panelIdx]
             ->addCommands($commands)
             ->getBody()
             ->att('id', $panelId.'-body');
        $this->panels[$panelIdx]->setClass('panel-body collapse' .(filter_input(\INPUT_POST, $this->id) == $panelIdx ? ' in' : ''));
        return $this->panels[$panelIdx];
    }
}
