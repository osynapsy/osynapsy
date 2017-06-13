<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\Component;
use Osynapsy\Core\Lib\Tag;

class DataGrid2 extends Component
{
    private $columns = [];
    private $title;
    
    public function __construct($name)
    {
        parent::__construct('div', $name);
        $this->att('class','container bcl-datagrid');
        $this->requireCss('/__assets/osynapsy/Bcl/DataGrid/style.css');
        $this->requireJs('/__assets/osynapsy/Bcl/DataGrid/script.js');
    }
    
    public function __build_extra__()
    {
        if (!empty($this->title)) {
            $this->add(
                $this->buildTitle($this->title)
            );
        }
        $this->add(
            $this->buildColumnHead()
        );
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
            $tr->add(new Tag('div'))
               ->att('class', $properties['class'].' hidden-xs bcl-datagrid-th')               
               ->add($label);
        }
        return $tr;
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
            $value = array_key_exists($properties['field'], $row) ? 
                     $row[$properties['field']] : 
                     '<label class="label label-warning">No data found</label>';            
            $cell = $tr->add(new Tag('div'))
                       ->att('class', 'bcl-datagrid-td');            
            $cell->add(
                $this->valueFormatting($value, $cell, $properties)
            );
        }
        if (!empty($row['_url_detail'])) {
            $tr->att('data-url-detail', $row['_url_detail']);
        }
        return $tr;
    }
    
    private function valueFormatting($value, &$cell, $properties)
    {        
        switch($properties['type']) {
            case 'money':
                $value = number_format($value, 2, ',', '.');
                $properties['class'] .= ' text-right';
                break;
        }
        if (!empty($properties['class'])) {
            $cell->att('class', $properties['class'], true);
        }
        return ($value == 0 && !empty($value)) ? $value : '&nbsp;';
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
}
