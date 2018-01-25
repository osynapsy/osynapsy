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

class DataGrid extends Component
{
    public $data = array();
    private $columns = array();
    private $part = array(
        'head' => null,
        'body' => null,
        'foot' => null
    );
    
    public function __construct($name)
    {
        parent::__construct('div', $name);
        $this->att('id', $name);
        $this->part['head'] = new Tag('div');
        $this->part['body'] = new Tag('div');
        $this->part['foot'] = new Tag('div');
    }
    
    public function __build_extra__()
    {
        $table = $this->add(
            new Tag('table')
        )->att(
            'class', 'table table-hover'
        );
        $table->add(
            $this->thead(
                array_keys($this->data[0])
            )
        );
        $tbody = $table->add(
            new Tag('tbody')
        );
        foreach ($this->data as $rec) {
            $tbody->add(
                $this->row($rec)
            );
        }
    }
    
    private function row($row)
    {
        $tr = new Tag('tr');
        $i = 0;
        foreach($row as $fieldName => $fieldValue) {
            $tr->add(
                $this->cellRow($i, $fieldName, $fieldValue)
            );
            $i++;
        }
        return $tr;
    }
    
    private function cellRow($pos, $fieldName, $fieldValue)
    {
        $td = new Tag('td');
        if (array_key_exists($pos, $this->columns)) {
            $function = $this->columns[$pos]['fncCellRow'];
            $function($fieldValue, $td);
        }
        $td->add($fieldValue);
        return $td;
    }
    
    private function cellHead($idx, $value) 
    {    
        $th = new Tag('th');
        if (!empty($this->columns[$idx]) && !empty($this->columns[$idx]['fncCellHead'])) {
            $function = $this->columns[$idx]['fncCellHead'];
            $function($value, $th);
        }        
        $th->add($value);
        return $th;
    }
    
    private function thead($rec)
    {
        $thead = new Tag('thead');
        $tr = $thead->add(new Tag('tr'));
        foreach ($rec as $idx => $columnTitle) {
            $columnTitle = $this->addColumn($idx, $columnTitle);
            $thead->add($this->cellHead($idx, $columnTitle));
        }
        return $thead;
    }
    
    public function setColumn($idx, $funcCellRow, $funcCellHead = null)    
    {
        $this->columns[$idx] = [
            'fncCellRow' => $funcCellRow,
            'fncCellHead' => $funcCellHead
        ];        
    }
    
    private function addColumn($idx, $columnTitle)
    {
        switch ($columnTitle[0]) {
            case '_':
                return null;
                break;
            case '$':
                $columnTitle = substr($columnTitle,1);
                $this->setColumn($idx, function(&$val, &$cel) {
                    $cel->att('class','text-right');
                    $val = number_format($val, 2, ',', '.');
                });
            break;
            default :
                
                break;
        }
        return $columnTitle;
    }
}
