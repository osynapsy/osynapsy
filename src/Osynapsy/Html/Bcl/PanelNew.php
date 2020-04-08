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
        'head' => 'panel-heading clearfix',
        'body' => 'panel-body',
        'foot' => 'panel-footer',
        'title' => 'panel-title'
    ];
    
    private $currentRow = null;
    private $currentColumn = null;
    private $title;
    private $commands = [];
    
    public function __construct($id, $title='', $class = ' panel-default', $tag = 'div')
    {
        parent::__construct($tag, $id);
        $this->classCss['main'] = 'panel'.$class;         
        $this->sections['body'] = new Tag('div');
        $this->title = $title;        
    }
    
    public function addCommands(array $commands = [])
    {
        $this->commands = array_merge($this->commands, $commands);        
        return $this;
    }
    
    protected function __build_extra__()
    {
        $this->buildTitle();
        $this->buildCommands();
        $this->att('class', $this->classCss['main']);
        foreach ($this->sections as $key => $section){
            if (empty($section)) {
                continue;
            }
            $section->att('class', $this->classCss[$key]);
            $this->add($section);
        }
    }
    
    protected function buildCommands()
    {
        if (empty($this->commands)) {
            return;
        }
        $container = $this->getHead()->add(
            new Tag('div', null, 'panel-commands pull-right')
        );         
        foreach($this->commands as $command) {
            $container->add($command);
        }        
    }
    
    protected function buildTitle()
    {
        if (empty($this->title)) {
            return;
        }               
        $this->getHead()->add(
            '<div class="'.$this->classCss['title'].' pull-left">'.$this->title.'</div>'
        );
    }
    
    public function addRow($class = 'row')
    {
        $this->currentRow = $this->sections['body']->add(
            new Tag('div', null, $class)
        );
        return $this->currentRow;
    }
    
    public function addColumn($colspan = 12, $offset = 0)
    {
        if (empty($this->currentRow)) {
            $this->addRow();
        }
        $this->currentColumn = $this->currentRow->add(
            new Column($colspan, $offset)
        );
        return $this->currentColumn;
    }
    
    public function getBody()
    {
        return $this->sections['body'];
    }
    
    public function getHead()
    {
        if (empty($this->sections['head'])) {
            $this->sections['head'] = new Tag('div');
        }
        return $this->sections['head'];
    }
    
    public function resetClass()
    {
        $this->setClass('','','','');
    }
    
    public function pushHorizontalField($label, $field, $info = '', $labelColumnWidth = 3)
    {        
        $this->classCss['main'] = 'form-horizontal';
        $row = $this->addRow()->att('class', 'form-group');
        $offset = $labelColumnWidth;
        if (!empty($label)) {
            $row->add(new Tag('label', null, sprintf('col-sm-%s control-label', $labelColumnWidth)))->add($label);
            $offset = 0;
        }
        if (!empty($label) && is_object($field)) {
            $field->att('data-label', $label);            
        }        
        $fieldContainer = $row->add(new Tag('div', null, sprintf('col-sm-%s col-sm-offset-%s', 12 - $labelColumnWidth, $offset)));
        $fieldContainer->add($field);
        if (!empty($info)) {
            $fieldContainer->add(new Tag('div'))->add($info);
        }        
    }
    
    public function setClass($body, $head = null, $foot = null, $main = null, $title = null)
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
        if (!is_null($title)) {
            $this->classCss['title'] = $title;
        }
        return $this;
    }        
}
