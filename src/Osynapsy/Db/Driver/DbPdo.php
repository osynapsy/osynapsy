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
    private $param = array();
    private $iCursor = null;
    public  $backticks = '"';
    
    public function __construct($str)
    {
        $par = explode(':',$str);
        switch ($par[0]) {
            case 'sqlite':
                $this->param['typ'] = trim($par[0]);
                $this->param['db']  = trim($par[1]);
                break;
            case 'mysql':
                $this->backticks = '`';
            default:
                $this->param['typ'] = trim($par[0]);
                $this->param['hst'] = trim($par[1]);
                $this->param['db']  = trim($par[2]);
                $this->param['usr'] = trim($par[3]);
                $this->param['pwd'] = trim($par[4]);
                $this->param['query-parameter-dummy'] = '?';
                break;
        }
    }
    
    public function begin()
    {
        $this->beginTransaction();
    }
    
    public function countColumn()
    {
       return $this->iCursor->columnCount();
    }
    
    public function connect()
    {
        $opt = array();
        switch ($this->param['typ']) {
            case 'sqlite':
                parent::__construct("{$this->param['typ']}:{$this->param['db']}");
                break;
            case 'mysql' :
                $opt[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
            default:
                $cnstr = "{$this->param['typ']}:host={$this->param['hst']};dbname={$this->param['db']}";
                parent::__construct($cnstr,$this->param['usr'],$this->param['pwd'], $opt);
                break;
        }
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function getType()
    {
        return $this->param['typ'];
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
    
    public function execQuery($sql, $par = null, $mth = null, $iColumn = null)
    {
        $this->iCursor = $this->prepare($sql);
        $this->iCursor->execute($par);
        switch ($mth) {
            case 'NUM':
                $mth = \PDO::FETCH_NUM;
                break;
            case 'ASSOC':
                $mth = \PDO::FETCH_ASSOC;
                break;
            case 'KEY_PAIR':
                $mth = \PDO::FETCH_KEY_PAIR;
                break;
            default :
                $mth = \PDO::FETCH_BOTH;
                break;
        }
        if (is_null($iColumn)) {
            $res = $this->iCursor->fetchAll($mth);
        } else {
            $res = $this->iCursor->fetchAll(\PDO::FETCH_COLUMN, $iColumn);
        }
        return $res;
    }

    public function execUnique($sql, $par = null, $mth = 'NUM')
    {
        $raw = $this->execQuery($sql, $par, $mth);       
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
        $stmt = is_null($stmt) ? $this->iCursor : $stmt;
        $cols = array();
        $ncol = $stmt->columnCount();
        for ($i = 0; $i < $ncol; $i++) {
            $cols[] = $stmt->getColumnMeta($i);
        }
        return $cols;
    }

    public function insert($tbl, array $arg)
    {
        $fld = $val = $arg2 = array();
        foreach ($arg as $k => $v) {
            $fld [] = $k;
            $val [] = '?';
            $arg2[] = $v;
        }
        $cmd = 'insert into '.$tbl.'('.implode(',',$fld).') values ('.implode(',',$val).')';
        $this->execCommand($cmd, $arg2);
        return $this->lastId();
    }

    public function multiInsert($table, array $rawValues, array $OnUpdateKey = [])
    {
        if (empty($rawValues[0]) || !is_array($rawValues[0])) {
            return;
        }
        $fields = $placeholder = array();
        foreach ($rawValues[0] as $k => $v) {
            $fields [] = $k;
            $placeholder[] = '?';
        }
        $arguments = '('.implode(',',$placeholder).')';
        $params = [];
        $values = [];
        foreach ($rawValues as $k => $record) {
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
        $command = 'update '.$table.' set '.implode(', ', $fields).' where '.implode(' and ', $where);
        //die($command);
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
    
    private function buildSelect($table, array $fields, array $conditions)
    {
        $sql = 'SELECT '. implode(',', $fields) . ' FROM ' . $table;
        if (empty($conditions)) {
            return $sql;
        }
        $where = $params = [];
        foreach($conditions as $field => $value) {
            $where[] = $field.' = :'.sha1($field);
            $params[sha1($field)] = $value;
        }
        
        $sql .= ' WHERE '.implode(' AND ', $where);    

        return [$sql, $params];
    }
    
    public function selectOne($table, array $conditions, array $fields = ['*'], $fetchMethod = 'ASSOC')
    {        
        list($sql, $params) = $this->buildSelect($table, $fields, $conditions);
        return $this->execUnique(
            $sql, 
            $params, 
            $fetchMethod
        );
    }
    
    public function par($p)
    {
        return array_key_exists($p,$this->param) ? $this->param[$p] : null;
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
}
