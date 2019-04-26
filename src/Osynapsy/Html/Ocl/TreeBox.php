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
    const ICON_NODE_CONNECTOR_EMPTY = '<span class="tree tree-null">&nbsp;</span>';
    const ICON_NODE_CONNECTOR_LINE = '<span class="tree tree-con-4">&nbsp;</span>';
    
    public function __construct($id)
    {
        parent::__construct('div', $id);
        $this->add(new HiddenBox("{$id}_sel"))->setClass('selectedNode');
        $this->add(new HiddenBox("{$id}_opn"))->setClass('openNodes');
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
    
    private function buildBranch($nodeId, $level, $position, $iconArray = [])
    {
        $branch = new Tag('div', $this->id.'_node_'.$nodeId, 'osy-treebox-node osy-treebox-branch');
        $branch->att(['data-level' => $level, 'data-node-id' => $nodeId]);        
        if (!empty($this->data[$nodeId])) {
            $label = $branch->add(new Tag('div', null, 'osy-treebox-node-label'));
            $label->add($this->buildIcon($nodeId, $position, $level, $iconArray));
            $label->add('<span class="osy-treebox-label">'.$this->data[$nodeId].'</span>');            
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
               //Fix for children begin on level major of one.                               
            } elseif (empty($idx) && $level < 1) {
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
        $leaf = new Tag('div', null, 'osy-treebox-node osy-treebox-leaf');
        $leaf->att(['data-level' => $level, 'data-node-id' => $nodeId]);                
        $leaf->add($this->buildIcon($nodeId, $position, $level, $iconArray));
        $leaf->add('<span class="osy-treebox-label">'.$this->data[$nodeId].'</span>');        
        return $leaf;
    }
    
    private function buildIcon($nodeId, $positionOnBranch, $level, $icons = [])
    {        
        $class = "osy-treebox-branch-command tree-plus-{$positionOnBranch}";
        if (!array_key_exists($nodeId, $this->treeData)){ 
            $class = "tree-con-{$positionOnBranch}";    
        } elseif (in_array($nodeId, $this->nodeOpenIds)) { //If node is open load minus icon
            $class .= ' minus';
        }
        //Sovrascrivo l'ultima icona con il l'icona/segmento corrispondente al comando / posizione        
        $icons[$level] = sprintf('<span class="tree %s">&nbsp;</span>', $class);        
        return implode('',$icons);
    }
    
    private function buildNode($nodeId, $level = 0, $position = 1, $icons = [])
    {
        if ($level > 0){
            $icons[$level] = $position === self::POSITION_END ? self::ICON_NODE_CONNECTOR_EMPTY: self::ICON_NODE_CONNECTOR_LINE;        
        }
        if (!empty($this->treeData[$nodeId])) {            
            return $this->buildBranch($nodeId, $level, $position, $icons);
        }
        return $this->buildLeaf($nodeId, $level, $position, $icons);
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
