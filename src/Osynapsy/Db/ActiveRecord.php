<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Db;

/**
 * Active record pattern implementation
 *
 * PHP Version 5
 *
 * @category Pattern
 * @package  Osynapsy
 * @author   Pietro Celeste <p.celeste@osynapsy.org>
 * @license  GPL http://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     http://docs.osynapsy.org/ref/ActiveRecord
 */

abstract class ActiveRecord
{
    private $activeRecord = [];
    protected $dbConnection;
    private $originalRecord = [];
    private $state = 'insert';
    private $sequence;
    private $table;    
    private $searchCondition = [];        
    private $softDelete = [];
    private $keys = [];
    private $fields = [];
    private $extensions = [];
    
    /**
     * Object constructor
     *
     * @param PDO $dbCn A valid dbPdo wrapper
     * @return void
     */
    public function __construct($dbCn) 
    {
        $this->dbConnection = $dbCn;
        $this->keys = $this->primaryKey();
        $this->table = $this->table();
        $this->sequence = $this->sequence();
        $this->fields = $this->fields();
        $this->softDelete = $this->softDelete();
        $this->init();
    }
    
    /**
     * Load record from database and store in originalRecord + activeRecord
     *
     * @param $reSearchParameters array of parameter (key = fieldname, value = value) ex.: ['id' => 5]
     * @return void
     */
    protected function find(array $reSearchParameters)
    {
        if (empty($reSearchParameters)) {
            throw new \Exception('Parameter required');
        }        
        $this->searchCondition = $reSearchParameters;
        $where = [
            'conditions' => [],
            'parameters' => []
        ];
        $range = range('a','z');
        $i = 0;
        foreach ($reSearchParameters as $field => $value) {
            $fieldsh1 = $range[$i];
            $where['conditions'][] = "$field = :{$fieldsh1}";
            $where['parameters'][$fieldsh1] = $value; 
            $i++;
        }
        $sql = "SELECT * FROM {$this->table} WHERE ".implode(' AND ', $where['conditions'])." ORDER BY 1";
        try {            
            $this->originalRecord = $this->activeRecord = $this->dbConnection->execUnique($sql, $where['parameters'], 'ASSOC');
        } catch (\Exception $e) {
            throw new \Exception('Query error : '.$sql."\n".$e->getMessage(), 100);
        }        
        $extendedValues = $this->findInExtensions();       
        if (!empty($extendedValues)) {
            $this->originalRecord = array_merge($this->originalRecord, $extendedValues);
            $this->activeRecord = array_merge($this->activeRecord, $extendedValues);
        }
        if (empty($this->originalRecord)) {
            return $this->originalRecord;
        }
        $this->state = 'update';
        return $this->activeRecord;
    }
    
    private function findInExtensions()
    {
        if (empty($this->extensions)) {
            return [];
        }        
        $values = [];       
        foreach($this->extensions as $extension){             
            $searchArray = [];
            foreach($this->keys as $idx => $field){
                $searchArray[$extension[1][$idx]] = $this->get($field);
            }            
            try {
                $values = array_merge($values, $extension[0]->findByAttributes($searchArray));
            } catch (Exception $e) {
                echo $e->getMessage();
            }            
        }        
        return $values;
    }
    
    /**
     * Find record in table through key value example : 1, [1,2]
     * 
     * @param int|string|array $keyValues
     * @return array
     * @throws \Exception
     */
    public function findByKey($keyValues)
    {        
        $this->reset();
        $raw = is_array($keyValues) ? $keyValues : [$keyValues];
        if (count($this->keys) != count($raw)) {
            throw new \Exception('Values don\'t match keys '.count($this->keys).' ('.count($raw).')', 202);
        }        
        $params = [];
        foreach($this->keys as $idx => $key) {
            if (!$raw[$idx]) {
                throw new \Exception('Values key is empty', 10);
            }
            $params[$key] = $raw[$idx]; 
        }
        return $this->find($params);
    }
    
    /**
     * Find record in table through array of attributes (example ['type' => 1])
     * 
     * @param array $reSearchParameters 
     * @return array
     */
    public function findByAttributes(array $reSearchParameters)
    {
        $this->reset();        
        return $this->find($reSearchParameters);        
    }
    
    /**
     * Get single value from active record or get all active record
     * 
     * @param string $key
     * @return mixed
     */
    public function get($key = null)
    {
        if (is_null($key)) {
            return $this->activeRecord;
        }
        if (is_array($this->activeRecord) && array_key_exists($key, $this->activeRecord)) {
            return $this->activeRecord[$key];
        }
        return false;
    }
    
    protected function init()
    {
    }
    
    /**
     * Set value on current active record
     * 
     * @param string $field 
     * @param string|int $value
     * @param string|int $defaultValue
     * @return $this
     * @throws \Exception
     */
    public function setValue($field, $value = null, $defaultValue = null)
    {
        if (empty($field)) {
            throw new \Exception('Field parameter is empty');
        }
        if (!in_array($field, $this->fields)) {
            $exists = $this->setValueInExtension($field, $value, $defaultValue);            
            if (!$exists) {
                throw new \Exception("Field {$field} do not exist");
            }
        }        
        $this->activeRecord[$field] = ($value !== '0' && $value !== 0 && empty($value))  ? $defaultValue : $value;        
        return $this;
    }
    
    private function setValueInExtension($field, $value = null, $defaultValue = null)
    {        
        if (empty($this->extensions)) {
            return false;
        }       
        foreach($this->extensions as $extension) {
            try{
                $extension[0]->setValue($field, $value, $defaultValue);
                return true;
            } catch(\Exception $e) {
                continue;
            }
        }        
        return false;
    }
    
    /**
     * Save current active record on database
     * 
     * @return string
     * @throws \Exception
     */
    public function save()
    {
        if (!$this->state) {
            throw new \Exception('Record is not updatable');
        }
        $this->beforeSave();
        $extensionValues = array_diff_key($this->activeRecord, array_flip($this->fields()));
        $id = empty($this->originalRecord)? $this->insert() : $this->update();
        $this->saveRecordExtensions($extensionValues);        
        $this->afterSave();
        return $id;
    }
    
    /**
     * Save current active record extension on database
     * 
     * @return void
     */
    private function saveRecordExtensions($values)
    {
        if (empty($this->extensions) || empty($values)) {
            return;
        }
        foreach($this->extensions as $extension){
            foreach($this->keys as $idx => $field){
                $extension[0]->setValue($extension[1][$idx], $this->get($field));
            }
            foreach($values as $field => $value) {
                try {
                    $extension[0]->setValue($field, $value);
                    $this->activeRecord[$field] = $value;
                    $this->originalRecord[$field] = $value;
                } catch (\Exception $e) {                    
                }
            }
            $extension[0]->save();
        }
    }

    /**
     * Insert current active record on database
     * 
     * @return string
     */
    private function insert()
    {
        $this->beforeInsert();        
        $sequenceId = $this->getSequenceNextValue();
        $autoincrementId = $this->dbConnection->insert(
            $this->table,
            array_intersect_key($this->activeRecord, array_flip($this->fields()))
        );
        $id = !empty($autoincrementId) ? $autoincrementId : $sequenceId;
        $this->loadRecordAfterInsert($id);
        $this->afterInsert($id);
        return $id;
    }        
    
    /**
     * After insert load record from db.
     * 
     * @return string
     */
    private function loadRecordAfterInsert($id)
    {
        if (!empty($id) && count($this->keys) == 1) {                     
            $this->findByKey($id);
            return;
        }
        $attributes = [];
        foreach($this->keys as $key) {
            if (!$this->activeRecord[$key]) {
                return;
            }
            $attributes[$key] = $this->activeRecord[$key];
        }
        $this->findByAttributes($attributes);
    }
    
    /**
     * Update current active record on database
     * 
     * @throws \Exception
     */
    private function update()
    {
        $this->beforeUpdate();
        if (empty($this->searchCondition)) {
            throw new \Exception('Primary key is empty');
        }
        $this->dbConnection->update(
            $this->table,
            array_intersect_key($this->activeRecord, array_flip($this->fields())),
            $this->searchCondition
        );
        $this->afterUpdate();
    }
    
    /**
     * Delete current active record from database
     * 
     * @throws \Exception
     */
    public function delete()
    {
        $this->beforeDelete();
        if (empty($this->searchCondition)) {
            throw new \Exception('Primary key is empty');
        }
        if (!empty($this->softDelete) && is_array($this->softDelete)) {
            $this->dbConnection->update(
                $this->table,
                $this->softDelete,
                $this->searchCondition
            );
        } else {
            $this->dbConnection->delete(
                $this->table,
                $this->searchCondition
            );
        }
        $this->afterDelete();
    }
    
    /**
     * Reset current active record
     * 
     * @return $this
     */
    public function reset()
    {
        $this->state = 'insert';
        $this->activeRecord = [];
        $this->originalRecord = [];
        $this->searchCondition = [];
        return $this;
    }
    
    /**
     * Get current db connection
     * 
     * @return DbPdo object
     */
    public function getDb()
    {
        return $this->dbConnection;
    }
    
    /**
     * Get current state of active record
     * 
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }
    
    /**
     * Get next value from sequence
     * 
     * @return string
     */
    protected function getSequenceNextValue()
    {
        if (empty($this->sequence)) {
            return null;
        }
        $firstKey = key(
            $this->keys
        );        
        $sequenceValue = $this->getDb()->execUnique(
            "SELECT {$this->sequence}.nextval FROM dual"
        );
        if (!empty($sequenceValue) && !empty($firstKey)) {
            $this->activeRecord[$firstKey] = $sequenceValue;
        }
        return $sequenceValue;
    }
    
    protected function extend($record, array $foreignKeys)
    {
        if (empty($foreignKeys)) {
            throw new \Exception("Parameter foreignKeys is empty");
        }
        $this->extensions[] = [$record, array_values($foreignKeys)];
    }
    
    /**
     * Get sequence
     * 
     * @return string
     */
    protected function sequence()
    {
        return '';
    }
    
    /**
     * Active or disactive softDelete
     * 
     * @return boolean
     */
    protected function softDelete()
    {
        return false;
    }
    
    protected function afterDelete(){}
        
    protected function afterInsert(){}
    
    protected function afterSave(){}
    
    protected function afterUpdate(){}
    
    protected function beforeDelete(){}     
        
    protected function beforeInsert(){}
    
    protected function beforeSave(){}
    
    protected function beforeUpdate(){}  
    
    abstract public function fields();
    
    abstract public function primaryKey();
        
    abstract public function table();
}
