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
        $this->add(
            "<style>"
            . "div.grid-cell:hover .card-command {display: block !important;}"
            . ".card-command {position: absolute; text-align: right; display: none; width: 94%; height: 100%; top: 0px; left: 0px; opacity: 0.7; background-color: white; border: 1px dotted gray; margin-left: 30px; padding: 3px;}"
            ."</style>"
         );
    }

    protected function __build_extra__(): void
    {
        if (empty($this->data)) {
            return;
        }
        if (empty($this->addCommand)) {
            array_unshift($this->data, $this->addCommand);
        }
        foreach ($this->data as $key => $rec) {
            $this->addCell($rec, $key, $this->cellSize);
        }
    }

    public function addCell($rec, $id = null, $size = 12, $class = null)
    {
        $Column = $this->getRow($size)->add(
            new Tag('div', null, trim("col-lg-{$size} $class"))
        );
        return $Column->add($this->buildCell($id, $rec));
    }

    public function addCellCommand($cell, $command)
    {
        if (empty($cell) || empty($command)) {
            return;
        }
        $cell->add(new Tag('div', null, 'card-command position-absolute'))->add($command);
    }

    private function buildCell($rawid, $rec)
    {
        $id = is_numeric($rawid) ? $this->id.'_cell_'.$rawid : $rawid;
        $Cell = new Tag('div', $id, 'grid-cell '.implode(' ',$this->cellClass));
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
        $this->data = $db->findAssoc($sql, $parameters);
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
