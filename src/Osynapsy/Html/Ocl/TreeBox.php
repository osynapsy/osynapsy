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
    
    private $nodeOpenIds = [];
    private $nodeSelectedId;
    
    const CLASS_SELECTED_LABEL = 'osy-treebox-label-selected';
    const ICON_NODE_CONNECTOR_EMPTY = '<span class="tree tree-null">&nbsp;</span>';
    const ICON_NODE_CONNECTOR_LINE = '<span class="tree tree-con-4">&nbsp;</span>';
    const POSITION_BEGIN = 1;
    const POSITION_BETWEEN = 2;
    const POSITION_END = 3;
    const ROOT_ID = '__ROOT__';
    
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
        $this->nodeOpenIds = $this->buildNodeOpenIds();
        $this->nodeSelectedId = filter_input(\INPUT_POST, "{$this->id}_sel");
        $nodeSelectedId = empty($_REQUEST["{$this->id}_open"]) ? self::ROOT_ID : $_REQUEST["{$this->id}_open"];
        $this->add($this->buildNode($nodeSelectedId));        
    }
    
    /**
     * Load open folder from post value
     * 
     * @return array
     */
    private function buildNodeOpenIds()
    {        
        $postIds = str_replace(
            ['][','[',']'],
            [',','',''],
            filter_input(\INPUT_POST, "{$this->id}_opn")
        );        
        $IDs = explode(',', $postIds);        
        $IDs[] = self::ROOT_ID;
        return $IDs;
    }
    
    private function buildBranch($nodeId, $level, $position, $iconArray = [])
    {
        $branch = new Tag('div', $this->id.'_node_'.$nodeId, 'osy-treebox-node osy-treebox-branch');
        $branch->att(['data-level' => $level, 'data-node-id' => $nodeId]);        
        if (!empty($this->data[$nodeId])) {
            $labelContainer = $branch->add(new Tag('div', null, 'osy-treebox-node-label'));
            $labelContainer->add($this->buildIcon($nodeId, $position, $level, $iconArray));
            $label = $labelContainer->add(new Tag('span', null, 'osy-treebox-label'));
            $label->add($this->data[$nodeId]);            
            if ($nodeId === $this->nodeSelectedId) {
                $label->att('class', self::CLASS_SELECTED_LABEL, true);
            }
        }
        $branchBody = $branch->add(new Tag('div', null, 'osy-treebox-node-body'));        
        if (!in_array($nodeId, $this->nodeOpenIds)) {
            $branchBody->att('class', 'hidden', true);
        }
        $branchBody->add(
            $this->buildChilds($nodeId, $level, $iconArray)
        );
        return $branch;
    }
    
    private function buildChilds($parentNodeId, $parentNodeLevel, $iconArray)
    {
        //Create a dummy tag to return and append to branch body
        $dummy = new Tag('dummy');
        //Get childs
        $childs = $this->treeData[$parentNodeId];
        //Calculate last child index
        $lastChildIdx = count($childs) - 1;
        foreach($childs as $currentChildIdx => $childrenId) {
            //Calcolo in che posizione si trova l'elemento (In testa = 1, nel mezzo = 2, alla fine = 3);
            $position = self::POSITION_BETWEEN;
            //Se il corrente children è anche l'ultimo
            if ($lastChildIdx === $currentChildIdx) {
                $position = self::POSITION_END;
               //Fix for children begin on level major of one.                               
            } elseif (empty($currentChildIdx) && $parentNodeLevel < 1) {
                $position = self::POSITION_BEGIN;
            }
            $dummy->add(
                $this->buildNode($childrenId, $parentNodeLevel + 1, $position, $iconArray)
            );            
        }        
        return $dummy;
    }
    
    private function buildLeaf($nodeId, $level, $position, $iconArray)
    {
        $leaf = new Tag('div', null, 'osy-treebox-node osy-treebox-leaf');
        $leaf->att(['data-level' => $level, 'data-node-id' => $nodeId]);                
        $leaf->add($this->buildIcon($nodeId, $position, $level, $iconArray));
        $label = $leaf->add(new Tag('span', null, 'osy-treebox-label'));
        $label->add($this->data[$nodeId]);
        if ($nodeId === $this->nodeSelectedId) {
            $label->att('class', self::CLASS_SELECTED_LABEL, true);
        }        
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
                $row[2] = self::ROOT_ID;                
            }if (!array_key_exists($row[2], $this->treeData)) {
                $this->treeData[$row[2]] = [];
            }            
            $this->treeData[$row[2]][] = $row[0];
            $data[$row[0]] = $row[1];
        }
        $this->setData($data);
    }
}
