<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc;

abstract class ModelEav extends Model
{    
    protected $field = array(
        'entityId' => null,
        'fieldName' => null,
        'fieldValue' => null
    );

    public function __construct($controller)
    {
        parent::__construct($controller);
        $this->set('actions.after-delete', null)
             ->set('actions.after-insert', null)      
             ->set('actions.after-update', null);
    }
    
    public function delete()
    {
        $this->beforeDelete();
        if ($this->controller->response->error()){ 
            return; 
        }
        $where = array();
        foreach ($this->repo->get('fields') as $field) {
            if ($field->isPkey()) { 
                $where[$field->name] =  $field->value;
            }
        }
        if (empty($where)) { 
            return; 
        }
        if (!empty($this->softdelete)) {
            $this->db->update( $this->repo->get('table'), $this->softdelete, $where);
        } else {
            $this->db->delete( $this->repo->get('table'), $where);
        }
        $this->afterDelete();        
    }
    
    public function insert($values, $where=null)
    {
        $this->beforeInsert();        
        if ($this->controller->response->error()) {
            return;
        }        
        $lastId = null;
         
        if ($this->sequence && is_array($where) && count($where) == 1) {
            $lastId = $values[$where[0]] = $this->db->execUnique("SELECT {$this->sequence}.nextval FROM DUAL",'NUM');
            $this->db->insert($this->get('table'), $values);
        } else {
            foreach ($this->values as $attribute => $value) {
                $par = array(
                    $this->field['attribute'] => $attribute,
                    $this->field['value'] => $value
                );
                $lastId = $this->db->insert($this->get('table'), $par);
            }
        }
        if (!empty($lastId)) {
            if ($pkField = $this->get('pkField')) {
                $pkField->setValue($lastId);
            }
        }
        $this->afterInsert($lastId);        
    }

    public function update($values, $where)
    {
        $this->beforeUpdate();
        if ($this->controller->response->error()) {
            return;
        }
        $values = array_diff_key($values, $where);

        foreach($values as $attribute => $value) {
            if (array_key_exists($attribute, $where)) {
                continue;
            }
            $parameter = array(               
               $this->field['value'] => $value
            );
            $where[$this->field['attribute']] = $attribute;            
            if ($this->db->update($this->get('table'), $parameter, $where)) {
                continue;                
            }
            $parameter = array_merge($parameter, $where);
            $this->db->insert( $this->get('table'), $parameter);
        }
        $this->afterUpdate();        
    }    

    public function find()
    {
        $sqlField = $sqlWhere = $sqlParam = array();
        $fields = $this->get('fields');
        
        $k=0;
        foreach ($fields as $field) {
            if ($field->isPkey()) {
                $sqlWhere[] = $field->name . ' = '.($this->db->getType() == 'oracle' ? ':'.$k : '?');
                $sqlParam[] = $field->value;
                $k++;
            } 
            $sqlField[] = $field->name;
        }
        
        if (empty($sqlWhere)){ 
            return; 
        }
        
        $sql  = " SELECT  {$this->field['attribute']}, {$this->field['value']}".PHP_EOL;        
        $sql .= " FROM  ".$this->repo->get('table')." ".PHP_EOL;
        $sql .= " WHERE ".implode(' AND ',$sqlWhere);
        try {
            $rs = $this->db->execQuery($sql, $sqlParam, 'NUM');
            foreach($rs as $rec) {
                if (!empty($rec)) {
                    $this->values[$rec[0]] = $rec[1];
                }
            }
        } catch (\Exception $e) {
            $this->controller->response->addContent('MODEL FIND ERROR: <pre>'.$e->getMessage()."\n".$sql.'</pre>');
        }
        $this->assocData();
    }
                   
    public function setEav($entityField, $attributeField, $valueField)
    {
        $this->field['entity'] = $entityField;
        $this->field['attribute'] = $attributeField;
        $this->field['value'] = $valueField;
    }            
}
