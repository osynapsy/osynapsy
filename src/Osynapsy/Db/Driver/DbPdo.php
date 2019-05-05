<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Db\Driver;

/**
 * Pdo wrap class
 *
 * PHP Version 5
 *
 * @category Driver
 * @package  Opensymap
 * @author   Pietro Celeste <p.celeste@osynapsy.org>
 * @license  GPL http://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     http://docs.osynapsy.org/ref/DbPdo
 */
class DbPdo extends \PDO implements InterfaceDbo
{    
    private $cursor = null;
    private $connectionStringDecoder = [
        'sqlite' => ['type','db'],
        '*' => ['type','host','dbname','username','password','port']
    ];
    private $param = [
        'queryParameterDummy' => '?', 
        'backticks' => '"'
    ];
    
    public function __construct($osyConnectionString)
    {
        $option = [];
        $pdoConnectionString = $this->buildPDOConnectionString($osyConnectionString);
        switch ($this->type) {
            case 'sqlite':
                parent::__construct("{$this->type}:{$this->dbname}");
                break;
            case 'mysql' :
                $option[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
                $this->backticks = '`';
            default:                
                parent::__construct($pdoConnectionString, $this->username, $this->password, $option);
                break;
        }
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }
    
    private function buildPDOConnectionString($osyConnectionString)
    {
        $cnParameters = explode(':', $osyConnectionString);
        if (empty($cnParameters)) {
            throw new \Exception('connection parameters is empty');
        }        
        $decoder = array_key_exists($cnParameters[0], $this->connectionStringDecoder) ? 
                   $this->connectionStringDecoder[$cnParameters[0]] : 
                   $this->connectionStringDecoder['*'];
        foreach($decoder as $propertyIdx => $property) {            
            if (!empty($cnParameters[$propertyIdx])) {
                $this->{$property} = $cnParameters[$propertyIdx];
            }            
        }
        $pdoConnectionString = "{$this->type}:host={$this->host};dbname={$this->dbname}";
        if ($this->port) {
            $pdoConnectionString .= ";port={$this->port}";
        }
        return $pdoConnectionString;
    }
    
    public function begin()
    {
        $this->beginTransaction();
    }
    
    public function countColumn()
    {
       return $this->cursor->columnCount();
    }        

    public function getType()
    {
        return $this->param['type'];
    }

    //Metodo che setta il parametri della connessione
    public function setParam($p, $v)
    {
        $this->param[$p] = $v;
    }

    //Prendo l'ultimo valore di un campo autoincrement dopo l'inserimento
    public function lastId()
    {
        return $this->lastInsertId();
    }
    
    public function execCommand($command, $parameters = null)
    {
        if (empty($parameters)) {
            return $this->exec($command);
        }    
        $s = $this->prepare($command);
        return $s->execute($parameters);
    }
    
    public function execMulti($cmd, $par)
    {
        $this->beginTransaction();
        $s = $this->prepare($cmd);
        foreach ($par as $rec) {
            try {
                $s->execute($rec);
            } catch (Exception $e){
                $this->rollBack();
                return $cmd.' '.$e->getMessage().print_r($rec, true);
            }
        }
        $this->commit();
        return;
    }
    
    public function execQuery($sql, $parameters = null, $fetchMethod = null, $fetchColumnIdx = null)
    {
        $this->cursor = $this->prepare($sql);
        $this->cursor->execute($parameters);
        if (!is_null($fetchColumnIdx)) {
            return $this->cursor->fetchAll(\PDO::FETCH_COLUMN, $fetchColumnIdx);
        } 
        switch ($fetchMethod) {
            case 'NUM':
                $pdoFetchMethod = \PDO::FETCH_NUM;
                break;
            case 'ASSOC':
                $pdoFetchMethod = \PDO::FETCH_ASSOC;
                break;
            case 'KEY_PAIR':
                $pdoFetchMethod = \PDO::FETCH_KEY_PAIR;
                break;
            default :
                $pdoFetchMethod = \PDO::FETCH_BOTH;
                break;
        }
        return $this->cursor->fetchAll($pdoFetchMethod);        
    }

    public function execUnique($sql, $parameters = null, $fetchMethod = 'NUM')
    {
        $raw = $this->execQuery($sql, $parameters, $fetchMethod);       
        if (empty($raw)) {
            return null;
        }
        $one = array_shift($raw);
        return count($one) == 1 ? array_values($one)[0] : $one;
    }
   
    public function fetch_all($rs)
    {
        return $rs->fetchAll(\PDO::FETCH_ASSOC);
    }
   
    public function getColumns($stmt = null)
    {
        if (is_null($stmt)) {
            $stmt = $this->cursor;
        }
        $cols = array();
        $ncol = $stmt->columnCount();
        for ($i = 0; $i < $ncol; $i++) {
            $cols[] = $stmt->getColumnMeta($i);
        }
        return $cols;
    }

    public function insert($table, array $parameters)
    {
        $fields = $placeholders = $values = [];
        foreach ($parameters as $field => $value) {
            $fields[] = $field;
            $placeholders[] = '?';
            $values[] = $value;
        }
        $sqlInsert = 'insert into '.$table.'('.implode(',',$fields).')';
        $sqlInsert .= ' values ('.implode(',',$placeholders).')';
        $this->execCommand($sqlInsert, $values);
        return $this->lastId();
    }

    public function multiInsert($table, array $rawValues, array $OnUpdateKey = [])
    {
        if (empty($rawValues[0]) || !is_array($rawValues[0])) {
            return;
        }
        $fields = $placeholder = array();
        foreach (array_keys($rawValues[0]) as $field) {
            $fields [] = $field;
            $placeholder[] = '?';
        }
        $arguments = '('.implode(',',$placeholder).')';
        $params = [];
        $values = [];
        foreach ($rawValues as $record) {
            $params[] = $arguments;
            $values = array_merge($values, array_values($record));
        }
        $command = 'INSERT INTO '.$table.'('.implode(',',$fields).') VALUES '.implode(',', $params);
        if (!empty($OnUpdateKey)) {
            array_walk($OnUpdateKey, function(&$item, $key) { $item = $key .' = '.$item; });
            $command .= ' ON DUPLICATE KEY UPDATE '.implode(' , ', $OnUpdateKey);
        }
        $this->execMulti($command, [$values]);
        return $this->lastId();
    }

    public function update($table, array $arg, array $filters)
    {
        $fields = $values = $where = [];
        foreach ($arg as $field => $value) {
            $fields[] = "{$field} = ?";
            $values[] = $value;
        }
        foreach ($filters as $field => $value) {
            if (!is_array($value)) {
                $where[] = $field . " = ?";
                $values[] = $value;
                continue;
            }
            $where[] = $field . ' IN (' .implode(',',array_fill(0, count($value), '?')) . ')';
            $values = array_merge($values, array_values($value));
        }
        $command = 'UPDATE '.$table.' SET '.implode(', ', $fields).' WHERE '.implode(' AND ', $where);        
        return $this->execCommand($command, $values);
    }

    public function delete($table, array $filters)
    {
        $values = $where = [];
        foreach ($filters as $field => $value) {
            if (!is_array($value)) {
                $where[] = $field . " = ?";
                $values[] = $value;
                continue;
            }
            $where[] = $field . ' IN (' .implode(',',array_fill(0, count($value), '?')) . ')';
            $values = array_merge($values, array_values($value));
        }
        $command = 'DELETE FROM '.$table.' WHERE '.implode(' AND ',$where);
        $this->execCommand($command, $values);
    }
    
    public function replace($table, $args, $conditions)
    {                        
        if ($this->selectOne($table, $conditions, ['count(*)'], 'NUM')) {
            $this->update($table, $args, $conditions);
            return;
        } 
        $this->insert($table, array_merge($args, $conditions));
    }
            
    public function selectOne($table, array $conditions, array $fields = ['*'], $fetchMethod = 'ASSOC')
    {        
        list($sql, $params) = $this->buildSelect($table, $fields, $conditions);
        return $this->execUnique($sql, $params, $fetchMethod);
    }
    
    private function buildSelect($table, array $fields, array $conditions)
    {
        $sql = 'SELECT '. implode(',', $fields) . ' FROM ' . $table;
        if (empty($conditions)) {
            return $sql;
        }
        $where = $params = [];
        foreach ($conditions as $field => $value) {
            $where[] = $field.' = :'.sha1($field);
            $params[sha1($field)] = $value;
        }        
        $sql .= ' WHERE '.implode(' AND ', $where);
        return [$sql, $params];
    }
    
    public function cast($field,$type)
    {
        $cast = $field;
        switch ($this->getType()) {
            case 'pgsql':
                $cast .= '::'.$type;
                break;
        }
        return $cast;
    }

    public function free_rs($rs)
    {
        unset($rs);
    }

    public function close()
    {
    }
    
    public function __get($key)
    {
        return array_key_exists($key, $this->param) ? $this->param[$key] : null;
    }
    
    public function __set($key, $value)
    {
        return $this->param[$key] = $value;
    }
}
