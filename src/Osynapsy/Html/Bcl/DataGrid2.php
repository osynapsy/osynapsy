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

class DataGrid2 extends Component
{
    private $columns = [];
    private $title;
    private $emptyMessage = 'No data found';
    private $showHeader = true;
    
    public function __construct($name)
    {
        parent::__construct('div', $name);
        $this->att('class','bcl-datagrid');
        $this->requireCss('Bcl/DataGrid/style.css');
        $this->requireJs('Bcl/DataGrid/script.js');
    }
    
    public function __build_extra__()
    {
        if (!empty($this->title)) {
            $this->add(
                $this->buildTitle($this->title)
            );
        }
        if ($this->showHeader) {
            $this->add(
                $this->buildColumnHead()
            );
        }
        $this->add(
            $this->buildBody()
        );
    }
    
    private function buildTitle($title)
    {        
        $tr = new Tag('div');
        $tr->att('class','row bcl-datagrid-title')
           ->add(new Tag('div'))
           ->att('class','col-lg-12')
           ->add($this->title);
        return $tr;
    }
    
    private function buildColumnHead()
    {
        $tr = new Tag('div');
        $tr->att('class', 'row bcl-datagrid-thead');
        foreach($this->columns as $label => $properties) {
            if (empty($label)) {
                continue;
            } elseif ($label[0] == '_') {
                continue;
            }
            $tr->add(new Tag('div'))
               ->att('class', $properties['class'].' hidden-xs bcl-datagrid-th')               
               ->add($label);
        }
        return $tr;
    }
    
    private function buildEmptyMessage($body)
    {
        $body->add(
            '<div class="row"><div class="col-lg-12 text-center">'.$this->emptyMessage.'</div></div>'
        );
        return $body;
    }
    
    private function buildBody()
    {
        $body = new Tag('div');
        $body->att('class','bcl-datagrid-body');        
        if (empty($this->data)) {
            return $this->buildEmptyMessage($body);
        }
        foreach ($this->data as $rec) {
            $body->add(
                $this->buildRow($rec)
            );
        }        
        return $body;
    }
    
    private function buildRow($row)
    {
        $tr = new Tag('div');
        $tr->att('class', 'row');
        foreach ($this->columns as $properties) {
            if (is_callable($properties['field'])) {
                $properties['function'] = $properties['field'];
                $value = null;
            } else {
                $value = array_key_exists($properties['field'], $row) ? 
                         $row[$properties['field']] : 
                         '<label class="label label-warning">No data found</label>';            
            }
            $cell = $tr->add(new Tag('div'))->att('class', 'bcl-datagrid-td');            
            $cell->add(
                $this->valueFormatting($value, $cell, $properties, $row, $tr)
            );
        }
        if (!empty($row['_url_detail'])) {
            $tr->att('data-url-detail', $row['_url_detail']);
        }
        return $tr;
    }
    
    private function valueFormatting($value, &$cell, $properties, $rec, &$tr)
    {        
        switch($properties['type']) {            
            case 'money':
                $value = number_format($value, 2, ',', '.');
                $properties['class'] .= ' text-right';
                break;
            case 'commands':
                $properties['class'] .= ' cmd-row';
                break;
        }        
        if (!empty($properties['function'])) {
            $value = $properties['function']($value, $cell, $rec, $tr);    
        }
        if (!empty($properties['class'])) {
            $cell->att('class', $properties['class'], true);
        }
        return ($value != 0 && empty($value)) ? '&nbsp;' : $value;
    }
    
    public function addColumn($label, $field, $class = '', $type = 'string',callable $function = null)
    {
        $this->columns[$label] = [
            'field' => $field,
            'class' => $class,
            'type' => $type,
            'function' => $function
        ];
        return $this;
    }
    
    public function hideHeader()
    {
        $this->showHeader = false;
        return $this;
    }
    
    public function setColumns($columns)
    {
        $this->columns = $columns;
        return $this;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    
    public function setEmptyMessage($message)
    {
        $this->emptyMessage = $message;
        return $this;
    }
}
