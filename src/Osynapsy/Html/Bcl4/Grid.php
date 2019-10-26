<?php
namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Component;
use Osynapsy\Html\Tag;

/**
 * Description of CardGrid
 *
 * @author Pietro
 */
class Grid extends Component
{
    protected $cellSize = 4;
    protected $cellClass = ['border', 'p-3', 'rounded'];
    protected $currentRow;
    protected $residualRowSize = 0;
    protected $rows = [];
    protected $formatValueFnc;
    protected $addCommand;
    
    public function __construct($id, $tag = 'div', $class = 'grid')
    {
        parent::__construct($tag, $id);
        $this->setClass($class);
        $this->setFormatValue(function($rec){
            return is_array($rec) ? implode('<br>',$rec) : $rec;
        });
    }
    
    protected function __build_extra__(): void
    {                
        if ($this->addCommand) {
            array_unshift($this->data, $this->addCommand);
        }
        foreach ($this->data as $key => $rec) {            
            $this->addColumn($key, $rec, $this->cellSize);            
        }
    }        
    
    public function addColumn($key, $rec, $size)
    {
        $Column = $this->getRow($size)->add(
            new Tag('div', null, 'col-lg-'.$size)
        );
        return $Column->add($this->buildCell($key, $rec));
    }
    
    public function addCellCommand($cell, $command)
    {
        if (empty($command)) {
            return;
        }       
        $container = $cell->add(new Tag('div', null, 'card-command position-absolute'));
        $container->att('style', 'top: 1px; right: 18px;')->add($command);        
    }
    
    private function buildCell($key, $rec)
    {
        $Cell = new Tag('div', $this->id.$key, 'grid-cell '.implode(' ',$this->cellClass));
        $fnc = $this->formatValueFnc;
        $Cell->add($fnc($rec, $Cell, $this));
        return $Cell;
    }        
    
    private function getRow($cellSize, $id = null)
    {
        if (empty($this->residualRowSize)) {
            $this->currentRow = $this->add(new Tag('div', $id, 'row mb-3 grid-row'));
            $this->residualRowSize = 12;
        }
        $this->residualRowSize -= $cellSize;
        return $this->currentRow;
    }

    public function setCellSize($size)
    {
        $this->cellSize = $size;
    }
    
    public function setCellClass($cellClass)
    {
        $this->cellClass = explode(' ',$cellClass);
    }
    
    public function setSql($db, $sql, array $parameters = [])
    {
        $this->db = $db;
        $this->data = $this->db->execQuery($sql, $parameters);
    }
    
    public function setAddCommand($command)
    {
        $this->addCommand = $command;
    }
    
    
    public function setFormatValue(callable $fnc)
    {
        $this->formatValueFnc = $fnc;
    }    
}
