<?php
/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Bcl4;


use Osynapsy\Html\Component;
use Osynapsy\Html\Tag;
use Osynapsy\Html\Bcl\Column;

class Card extends Component
{
    private $sections = array(
        'header' => null,
        'body' => null,
        'footer' => null
    );
    
    private $classCss = [
        'main' => 'card',
        'head' => 'card-header',
        'body' => 'card-body',
        'foot' => 'card-footer',
        'title' => 'card-title',
        'row'   => 'row',
        'cell'  => ''    
    ];
    
    private $currentRow = null;
    private $currentColumn = null;
    private $title;
    private $commands = [];
    
    public function __construct($id, $class = 'card', $tag = 'div')
    {
        parent::__construct($tag, $id);
        $this->classCss['main'] = $class;         
        $this->sections['body'] = new Tag($tag);      
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
        $this->getBody()->add(
            '<div class="'.$this->classCss['title'].'">'.$this->title.'</div>'
        );
    }
    
    public function addRow()
    {
        $this->currentRow = $this->sections['body']->add(
            new Tag('div', null, $this->classCss['row'])
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
        )->setClass($this->classCss['cell']);                        
        return $this->currentColumn;
    }
    
    public function getBody()
    {
        return $this->sections['body'];
    }
    
    public function getHead()
    {
        if (empty($this->sections['header'])) {
            $this->sections['header'] = new Tag('div');
        }
        return $this->sections['head'];
    }
    
    public function getRow()
    {
        return $this->currentRow;
    }
        
    public function resetClass()
    {
        $this->setClass('','','','');
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
    
    public function addClass($class)
    {
        $this->classCss['main'] .= ' '.$class;
        return $this;
    }
    
    public function addClassRow($class)
    {
        $this->classCss['row'] .= ' '.$class;
    }
    
    public function addClassCell($class)
    {
        $this->classCss['cell'] .= ' '.$class;
    }
    
    public function noPadding()
    {
        $this->addClassRow('no-gutters');
    }
    
    public function setHeight100()
    {
        $this->addClass('h-100 d-line-block');
    }
    
    public function setText($text)
    {
        if (empty($text)) {
            return;
        }
        $this->getBody()->add('<p class="card-text">'.$text.'</p>');
    }
    
    public function setTitle($title, $tag = 'h5', $class = '')
    {        
        $this->getBody()->add(new Tag($tag, null, trim('card-title '.$class)))->add($title);        
    }
    
    public function setCommand($command)
    {
        $this->setClass('position-relative mr-3');
        $container = $this->add(new Tag('div', null, 'card-command position-absolute'));        
        $container->att('style', 'top: 5px; right: 5px;')->add($command);        
    }
        
    public function simulateTable(bool $padding = true)
    {
        $this->classCss['body'] .= ' d-table';
        $this->addClassRow('d-table-row');
        $this->addClassCell('d-table-cell');
        if (!$padding) {
            $this->noPadding();
        }
    }
}
