<?php
namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Tag;
use Osynapsy\Html\Bcl\DataGrid2;

/**
 * Description of DataGrid2Column
 *
 * @author pietr
 */
class DataGrid2Column 
{        
    const FIELD_TYPE_MONEY = 'money';
    const FIELD_TYPE_CHECKBOX = 'check';
    const FIELD_TYPE_COMMAND = 'commands';
    
    private $properties = [        
        'dimension' => [
            'xs' => 12,
            'sm' => 12,
            'md' => 2,
            'lg' => 2,
            'xl' => 2
        ],
        'type' => 'string',
        'builder' => null,
        'class' => null,        
        'label' => '&nbsp;',        
    ];
    private $parentId;
    
    public function __construct(
        $label, 
        $field,
        $class = '',
        $type = 'string',
        callable $function = null,
        $fieldOrderBy = null
    ){
        $this->properties['label'] = $label;
        $this->properties['field'] = $field;
        $this->properties['type'] = $type;
        $this->properties['class'] = $class;                
        $this->properties['builder'] = $function;
        $this->properties['fieldOrderBy'] = $fieldOrderBy;
    }
    
    private function builCheckBoxLabel()
    {
        return '<span class="fa fa-check bcl-datagrid-th-check-all" data-field-class="'.$this->parentId.''.$this->properties['field'].'"></span>';
    }
    
    public function buildTh($paginationOrderByFields)
    {
        $rawLabel = $this->properties['label'];
        if (empty($rawLabel)) {
            return;
        } elseif ($rawLabel[0] == '_') {
            return;
        }        
        if ($this->properties['type'] === self::FIELD_TYPE_CHECKBOX) {            
            $rawLabel = $this->builCheckBoxLabel();
        }
        $th = new Tag('div', null, $this->properties['class'].' bcl-datagrid-th');
        $th->add(new Tag('span'))->add($rawLabel);
        if (empty($paginationOrderByFields) || $this->properties['type'] === 'check') {
            return $th;
        }
        $orderByField = $this->properties['fieldOrderBy'];
        $th->att('data-idx', $orderByField)->att('class', 'bcl-datagrid-th-order-by', true);
        foreach ([$orderByField, $orderByField.' DESC'] as $i => $token) {
            $key = array_search($token, $paginationOrderByFields);
            if ($key !== false) {
                $icon = ($key + 1).' <i class="fa fa-arrow-'.(empty($i) ? 'up' : 'down').'"></i>';
                $th->add('<span class="bcl-datagrid-th-order-label">'.$icon.' </span>');
            }
        }
        return $th;
    }
    
    public function buildTd(Tag $tr, array $record)
    {
        $properties = $this->properties;
        if (is_callable($properties['field'])) {
            $properties['function'] = $properties['field'];
            $value = null;
        } elseif (!array_key_exists($properties['field'], $record)) {
            $value = '<label class="label label-warning">No data found</label>';            
        } else {
            $value = $record[$properties['field']]; 
        }
        $td = new Tag('div', null, 'bcl-datagrid-td');            
        $td->add($this->valueFormatting($value, $td, $properties, $record, $tr));
        return $td;
    }
    
    /**
     * Format a value of cell for correct visualization
     * 
     * @param string $value to format.
     * @param object $cell container of value
     * @param type $properties 
     * @param type $rec record which contains value.
     * @param type $tr row container object
     * @return string
     */
    public function valueFormatting($value, &$cell, $properties, $rec, &$tr)
    {        
        switch($properties['type']) {
            case self::FIELD_TYPE_CHECKBOX:
                if (empty($value)) {
                    break;
                }                       
                $value = $this->buildCheckBox($value);                
                break;
            case self::FIELD_TYPE_MONEY:
                $value = is_numeric($value) ? number_format($value, 2, ',', '.') : $value;
                $properties['class'] .= ' text-right';
                break;
            case self::FIELD_TYPE_COMMAND:
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
    
    private function buildCheckBox($value)
    {
        $class = $this->parentId.''.$this->properties['field'];        
        $checkbox = new Tag('input');
        $checkbox->att([
            'type' => 'checkbox',
            'name' => $class.'['.$value.']',
            'class' => $class,
            'value' => $value
        ]);
        if (!empty($_POST[$class]) && !empty($_POST[$class][$value])) {
            $checkbox->att('checked','checked');
        }
        return $checkbox->get();
    }
    
    public function setParent($id)
    {
        $this->parentId = $id;
    }
}
