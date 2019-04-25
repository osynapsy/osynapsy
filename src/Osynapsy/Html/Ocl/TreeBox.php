<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Ocl;

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;
use Osynapsy\Html\Ocl\HiddenBox;



/**
 * Description of TreeBox
 *
 * @author Pietro Celeste <p.celeste@osynapsy.org>
 */
class TreeBox extends Component
{
    private $treeData = [
        '__ROOT__' => []
    ];
    private $rootId = '__ROOT__';
    private $nodeOpenIds = [];
    
    const POSITION_BEGIN = 1;
    const POSITION_BETWEEN = 2;
    const POSITION_END = 3;
    
    public function __construct($id)
    {
        parent::__construct('div', $id);
        $this->add(new HiddenBox("{$id}_selected"));
        $this->add(new HiddenBox("{$id}_opened"));
        $this->setClass('osy-treebox');
        $this->requireJs('Ocl/TreeBox/script.js');
        $this->requireCss('Ocl/TreeBox/style.css');
    }
    
    protected function __build_extra__()
    {
        $this->buildTreeData();        
        $this->nodeOpenIds = [$this->rootId];
        $nodeSelectedId = empty($_REQUEST["{$this->id}_open"]) ? $this->rootId : $_REQUEST["{$this->id}_open"];
        $this->add($this->buildNode($nodeSelectedId));        
    }
    
    private function buildNode($nodeId, $level = 0, $position = 1, $iconArray = [])
    {
        $iconArray[$level] = empty($level) ? null : 4;
        if ($position === self::POSITION_END) {            
            $iconArray[$level] = null;
        //Fix for children begin on level major of one.
        } elseif ($position === self::POSITION_BEGIN && $level > 1) {
            $position = self::POSITION_BETWEEN;
        }
        if (!empty($this->treeData[$nodeId])) {            
            return $this->buildBranch($nodeId, $level, $position, $iconArray);
        }
        return $this->buildLeaf($nodeId, $level, $position, $iconArray);
    }
    
    private function buildBranch($nodeId, $level, $position, $iconArray = [])
    {
        $branch = new Tag('div', 'osy-treebox-node-'.$nodeId, 'osy-treebox-branch');
        $branch->att('data-level', $level);        
        if (!empty($this->data[$nodeId])) {
            $label = $branch->add(new Tag('label', null, 'osy-treebox-node-label'));
            $label->add($this->buildIcon($nodeId, $position, $level, $iconArray).$this->data[$nodeId]);            
        }
        $branchBody = $branch->add(new Tag('div', null, 'osy-treebox-node-body'));        
        if (!in_array($nodeId, $this->nodeOpenIds)) {
            $branchBody->att('class', 'hidden', true);
        }
        //Calcolo l'indice dell'utlimo elemento del ramo.
        $lastIdx = count($this->treeData[$nodeId]) - 1;
        foreach($this->treeData[$nodeId] as $idx => $childrenId) {
            //Calcolo in che posizione si trova l'elemento (In testa = 1, nel mezzo = 2, alla fine = 3);
            $position = self::POSITION_BETWEEN;
            //Se il corrente children è anche l'ultimo
            if ($lastIdx === $idx) {
                $position = self::POSITION_END;
            //Se l'indice corrente è vuoto (ossia è il first children) e il livello è 0            
            } elseif (empty($idx)) {
                $position = self::POSITION_BEGIN;
            }
            $branchBody->add(
                $this->buildNode($childrenId, $level + 1, $position, $iconArray)
            );            
        }        
        return $branch;
    }
    
    private function buildLeaf($nodeId, $level, $position, $iconArray)
    {
        $leaf = new Tag('label', null, 'osy-treebox-leaf label-block');
        $leaf->att('data-level', $level);
        $leaf->add($this->buildIcon($nodeId, $position, $level, $iconArray).$this->data[$nodeId]);        
        return $leaf;
    }
    
    private function buildIcon($nodeId, $positionOnBranch, $level, $iconArray = [])
    {        
        $icon = '';
        for($idx = 1; $idx < $level; $idx++) {
            $class  = empty($iconArray[$idx]) ? 'tree-null' : ' tree-con-'.$iconArray[$idx];
            $icon .= '<span class="tree '.$class.'">&nbsp;</span>';
        }
        $class = 'tree-con-';
        if (array_key_exists($nodeId, $this->treeData)){ 
            $class = 'osy-treebox-branch-command tree-plus-';
        }
        $class .= $positionOnBranch;
        if (in_array($nodeId, $this->nodeOpenIds)) {                    
            $class .= ' minus';
        }
        $icon .= '<span class="tree '.$class.'">&nbsp;</span>';        
        return $icon;
    }
    
    private function buildTreeData()
    {        
        $data = [];
        foreach ($this->getData() as $rawRow) {
            $row = array_values($rawRow);            
            if (empty($row[2])) {
                $row[2] = $this->rootId;                
            }if (!array_key_exists($row[2], $this->treeData)) {
                $this->treeData[$row[2]] = [];
            }            
            $this->treeData[$row[2]][] = $row[0];
            $data[$row[0]] = $row[1];
        }
        $this->setData($data);
    }
}
