<?php
namespace Osynapsy\Core\Driver;

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
                $opt[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8";
            default:
                try{
                    $cnstr = "{$this->param['typ']}:host={$this->param['hst']};dbname={$this->param['db']}";
                    //var_dump($cnstr);
                    parent::__construct($cnstr,$this->param['usr'],$this->param['pwd'], $opt);
                } catch (\Exception $e) {
                    die($cnstr.' '.$e);
                }
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
    
    public function execCommand($cmd, $par = null)
    {
        if (!empty($par)) {
            $s = $this->prepare($cmd);
            return $s->execute($par);
        } else {
            return $this->exec($cmd);
        }
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
        $res = $this->execQuery($sql,$par,$mth);
        if (empty($res)) {
            return null;
        }
        if (count($res) > 1) {
            $res = $res[0];
        }
        return (count($res)== 1 && count($res[0])==1) ? $res[0][0] : $res[0];
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
        $fld = $val = array();
        foreach ($arg as $k=>$v) {
            $fld [] = $k;
            $val [] = '?';
            $arg2[] = $v;
        }
        $cmd = 'insert into '.$tbl.'('.implode(',',$fld).') values ('.implode(',',$val).')';
        $this->execCommand($cmd, $arg2);
        return $this->lastId();
    }

    public function update($tbl, array $arg, array $cnd)
    {
        $fld = array();
        foreach ($arg as $k => $v) {
            $fld[] = "{$k} = ?";
            $val[] = $v;
        }
        if (!is_array($cnd)) {
          $cnd = array('id' => $cnd);
        }
        $whr = array();
        foreach ($cnd as $k => $v) {
            $whr[] = "$k = ?";
            $val[] = $v;
        }
        $cmd .= 'update '.$tbl.' set '.implode(', ', $fld).' where '.implode(' and ', $whr);
        // mail('p.celeste@spinit.it','query',$cmd."\n".print_r($val,true));
        return $this->execCommand($cmd,$val);
    }

    public function delete($tbl, array $cnd)
    {
        $whr = array();
        if (!is_array($cnd)) {
            $cnd = array('id'=>$cnd);
        }
        foreach ($cnd as $k=>$v) {
            $whr[] = "{$k} = ?";
            $val[] = $v;
        }
        $cmd .= 'delete from '.$tbl.' where '.implode(' and ',$whr);
        $this->execCommand($cmd, $val);
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
