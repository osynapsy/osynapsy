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

use Osynapsy\Html\Component;
use Osynapsy\Html\Tag;

class PanelNew extends Component
{
    private $sections = array(
        'head' => null,
        'body' => null,
        'foot' => null
    );
    
    private $classCss = [
        'main' => 'panel',
        'head' => 'panel-heading',
        'body' => 'panel-body',
        'foot' => 'panel-footer'
    ];
    
    private $currentRow = null;
    private $currentColumn = null;
    
    public function __construct($id, $title='', $class = ' panel-default', $tag = 'div')
    {
        parent::__construct($tag, $id);
        $this->classCss['main'] = 'panel'.$class;
        if (!empty($title)) {
            $this->sections['head'] = new Tag('div');
            $this->sections['head']->add('<h4 class="panel-title">'.$title.'</h4>');
        }
        $this->sections['body'] = new Tag('div');        
    }
    
    protected function __build_extra__()
    {
        $this->att('class', $this->classCss['main']);
        foreach ($this->sections as $key => $section){
            if (empty($section)) {
                continue;
            }
            $section->att('class', $this->classCss[$key]);
            $this->add($section);
        }
    }
    
    public function addRow()
    {
        $this->currentRow = $this->sections['body']
                                 ->add(new Tag('div'))
                                 ->att('class','row');
        return $this->currentRow;
    }
    
    public function addColumn($colspan = 12, $offset = 0)
    {
        if (empty($this->currentRow)) {
            $this->addRow();
        }
        $this->currentColumn = $this->currentRow->add(new Column($colspan, $offset));
        return $this->currentColumn;
    }
    
    public function getBody()
    {
        return $this->sections['body'];
    }
    
    public function setClass($body, $head = null, $foot = null, $main = null)
    {
        $this->classCss['body'] = $body;
        if (!is_null($head)) {
            $this->classCss['head'] = $head;
        }
        if (!is_null($foot)) {
            $this->classCss['foot'] = $foot;
        }
        if (!is_null($main)) {
            $this->classCss['main'] = $main;
        }        
        return $this;
    }
    
    public function resetClass()
    {
        $this->setClass('','','','');
    }
}
