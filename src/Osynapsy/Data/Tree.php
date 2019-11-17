<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Osynapsy\Data;

/**
 * Description of Tree
 *
 * @author Pietro
 */
class Tree
{
    private $keyId;
    private $keyParent;
    private $keyIsOpen;
    private $dataSet;
    private $tree;
    
    public function __construct($idKey, $parentKey, $isOpenKey, array $dataSet = [])
    {
        $this->keyId = $idKey;
        $this->keyParent = $parentKey;
        $this->keyIsOpen = $isOpenKey;
        $this->setDataset($dataSet);
    }
    
    protected function init()
    {
        $rawDataSet = [];
        foreach ($this->dataSet as $rec){
            $rawDataSet[$rec[$this->keyParent] ?? 0][] = $rec;
        }        
        return $rawDataSet;
    }   
    
    protected function build(&$rawDataSet, $parentId = 0)
    {
        $branch = [];
        foreach ($rawDataSet[$parentId] as $child){
            $childId = $child[$this->keyId];            
            $child['parent'] = $parentId;            
            if(!empty($rawDataSet[$childId])){
               $child['childrens'] = $this->build($rawDataSet, $childId);
            }
            $branch[] = $child;
        } 
        return $branch;
    }
    
    public function get()
    {
        if (is_null($this->tree)) {
            $rawDataSet = $this->init();            
            $this->tree = $this->build($rawDataSet);
        }
        return $this->tree;
    }
    
    public function setDataset(array $dataset)
    {
        $this->dataSet = $dataset;
    }
}
