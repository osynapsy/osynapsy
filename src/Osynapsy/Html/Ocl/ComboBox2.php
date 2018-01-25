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

use Osynapsy\Html\Tag as Tag;
use Osynapsy\Html\Component;

/**
 * Description of ComboBox2
 *
 * @author Pietro Celeste
 */
class ComboBox2 extends Component
{    
    private $dataGroup;
    private $dataRequest;
    public  $isTree = false;
    
    //put your code here
    public function __construct($name)
    {
        parent::__construct('select', $name);
        $this->name = $name;
    }
    
    public function __build_extra__()
    {
        $this->getRequestValue();
        if ($this->isTree) {
            $this->buildTree();
        } else {
            $this->buildLinear();
        }
    }

    private function buildLinear()
    {                
        if (!$this->multiple) {
            $this->addOption('','- select -');
        }
        foreach ($this->data as $raw) {
            $record = array_values($raw);            
            $this->addOption($record[0], $record[1]);
        }
    }
    
    private function buildTree()
    {        
        $dataRoot = array();       
        foreach ($this->data as $raw) {
            $record = array_values($raw);
            if (empty($record[2])) {
                $dataRoot[] = $record;
                continue;
            } 
            if (array_key_exists($record[2], $this->dataGroup)) {
                $this->dataGroup[$record[2]][] = $record;    
                continue;
            }
            $this->dataGroup[$record[2]] = array($record);            
        }
        $this->buildTreeBranch($dataRoot);
    }
    
    private function buildTreeBranch(array $data, $level = 0)
    {
        if (empty($data)) {
            return;
        }
        foreach ($data as $rec) {
            list($value, $label) = array_slice($rec, 0, 2);
            $label = str_repeat('&nbsp;',$level*5) . $this->nvl($label, $value);
            $this->addOption($value, $label);            
            if (array_key_exists($value, $this->dataGroup)) {
                $this->buildBranch($this->dataGroup[$value], $level+1);
            }
        }
    }
    
    private function addOption($value, $label)
    {
        $option = $this->add(new Tag('option'));
        $option->att('value',$value)->add($label);
        if (in_array($value, $this->dataRequest)) {
            $option->att('selected', 'selected');
        }
        return $option;
    }
    
    private function getRequestValue()
    {
        $fieldName = $this->multiple ? str_replace('[]','',$this->name) : $this->name;               
        $dataRequest = $this->getGlobal($fieldName, $_REQUEST);
        $this->dataRequest = is_array($dataRequest) ? $dataRequest : array($dataRequest);        
    }
}
