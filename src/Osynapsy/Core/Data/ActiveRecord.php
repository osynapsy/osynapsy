<?php
namespace Osynapsy\Core\Data;

abstract class ActiveRecord
{
    private $dbConnection;
    private $table;
    private $sequence;
    private $searchCondition = [];
    private $activeRecord = [];
    private $originalRecord = [];
    private $softDelete = [];
    private $keys = [];
    private $fields = [];
    private $updatable = true;
    
    public function __construct($dbCn) 
    {
        $this->dbConnection = $dbCn;
        $this->keys = $this->primaryKey();
        $this->table = $this->table();
        $this->sequence = $this->sequence();
        $this->fields = $this->fields();
        $this->softDelete = $this->softDelete();
    }
    
    protected function find($reSearchParameters)
    {
        if (empty($reSearchParameters)) {
            throw new \Exception('Parameter required');
        }
        $this->searchCondition = $reSearchParameters;
        
        $where = array_map(
            function($field) {
                return "$field = :{$field}";
            },
            array_keys(
                $reSearchParameters
            )
        );
        $this->originalRecord = $this->activeRecord = $this->dbConnection->execUnique(
            "SELECT * FROM {$this->table} WHERE ".implode(' AND ', $where)." ORDER BY 1",
            $reSearchParameters, 
            'ASSOC'
        );
        return $this->activeRecord;
    }
    
    public function findByKey($keyValues)
    {        
        $raw = is_array($keyValues) ? $keyValues : [$keyValues];
        if (count($this->keys) != count($raw)) {
            throw new \Exception('Values don\'t match keys');
        }        
        $params = [];
        foreach($this->keys as $idx => $key) {
            $params[$key] = $raw[$idx]; 
        }
        $this->updatable = true;
        return $this->find($params);
    }
    
    public function findByAttributes(array $reSearchParameters)
    {
        $this->updatable = false;
        return $this->find($reSearchParameters);        
    }
    
    public function get($key = null)
    {
        if (is_null($key)) {
            return $this->activeRecord;
        }
        if (array_key_exists($key, $this->activeRecord)) {
            return $this->activeRecord[$key];
        }
        return false;
    }
    
    public function setValue($field, $value)
    {
        if (!in_array($field, $this->fields)) {
            throw new \Exception('Field do not exist');
        }
        $this->activeRecord[$field] = $value;
        return $this;
    }
    
    public function save()
    {
        if (empty($this->updatable)) {
            throw new \Exception('Record is not updatable');
        }
        $this->beforeSave();
        if (empty($this->originalRecord)) {
            $this->insert($this->activeRecord);
        } else {
            $this->update($this->activeRecord);
        }
        $this->afterSave();
    }
    
    private function insert()
    {
        $this->beforeInsert();        
        $sequenceId = $this->getSequenceNextValue();                
        $autoincrementId = $this->dbConnection->insert(
            $this->table,
            $this->activeRecord
        );
        $id = !empty($autoincrementId) ? $autoincrementId : $sequenceId;
        if (!empty($id)) {
            $this->findByKey($id);
        }
        $this->afterInsert($id);
    }
    
    private function update()
    {
        $this->beforeUpdate();
        if (empty($this->searchCondition)) {
            throw new \Exception('Primary key is empty');
        }
        $this->dbConnection->update($this->table, $this->activeRecord, $this->searchCondition);
        $this->afterUpdate();
    }
    
    public function delete()
    {
        $this->beforeDelete();
        if (empty($this->searchCondition)) {
            throw new \Exception('Primary key is empty');
        }
        $this->dbConnection->delete(
            $this->table,
            $this->searchCondition
        );
        $this->afterDelete();
    }
    
    public function reset()
    {
        $this->updatable = true;
        $this->activeRecord = [];
        $this->originalRecord = [];
        $this->searchCondition = [];
        return $this;
    }
    
    protected function getSequenceNextValue()
    {
        if (empty($this->sequence)) {
            return null;
        }
        $firstKey = key(
            $this->keys
        );        
        $sequenceValue = $this->db->execUnique(
            "SELECT {$sequence}.nextval FROM dual"
        );
        if (!empty($sequenceValue) && !empty($firstKey)) {
            $this->activeRecord[$firstKey] = $sequenceValue;
        }
        return $sequenceValue;
    }
    
    protected function sequence()
    {
        return '';
    }
    
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
    
    abstract protected function fields();
    
    abstract protected function primaryKey();
        
    abstract protected function table();
}
