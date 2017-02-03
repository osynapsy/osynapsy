<?php
namespace Osynapsy\Ocl\Component;

use Osynapsy\Core\Kernel as Kernel;
use Osynapsy\Core\Lib\Tag as Tag;

//costruttore del combo box
class ComboBox extends Component
{
    public $__dat = array();
    public $__grp = array();
    public $isTree = false;
    public $dba = null;
    public $placeholder = '- Seleziona -';
    
    public function __construct($nam, $id=null, $db = null)
    {
        parent::__construct('select',$this->nvl($id,$nam));
        $this->att('name',$nam);        
        $this->dba = empty($db) ? Kernel::$dba : $db;
    }
    
    protected function __build_extra__()
    {
        if (empty($this->__dat) && $sql = $this->get_par('datasource-sql')) {
            try {
                $this->__dat = $this->dba->execQuery($sql, $this->get_par('datasource-sql-par'), 'BOTH');
            } catch(\Exception $e) {
                $this->att(0,'dummy');
                $this->add('<div class="osy-error" id="'.$this->id.'">SQL ERROR - [LABEL]</div>');
                $this->add('<div class="osy-error-msg">'.($e->getMessage()).'<br>'.nl2br($sql).'</div>');
                return;
            }
        }
        if (!empty($this->__dat) && $this->isTree && array_key_exists(2,$this->__dat[0])) {
            if (!$this->get_par('option-select-disable')){  array_unshift($this->__dat,array('','- select -','_group'=>'')); }
            $this->buildTree($this->__dat);
        } else {
            if (!$this->get_par('option-select-disable')){ 
                if ($lbl = $this->get_par('label-inside')){
                    $this->placeholder = $lbl;
                }
                array_unshift($this->__dat,array('',$this->placeholder)); 
            }
            $val = $this->getGlobal($this->name,$_REQUEST);
            $idx = array(0,1);
            if ($this->get_par('fields-order')) {
                $idx = explode(',',$this->get_par('fields-order'));
            }
            foreach ($this->__dat as $k => $itm) {
                $sel = ($val == $itm[$idx[0]]) ? ' selected' : '';
                $opt = $this->add(new Tag('option'))->att('value',$itm[$idx[0]]);
                $opt->add($this->nvl($itm[$idx[1]],$itm[$idx[0]]));
                if ($val == $itm[$idx[0]]){
                    $opt->att('selected','selected');
                }
                //$this->add('<option value="'.$itm[$idx[0]].'"'.$sel.'>'.nvl($itm[$idx[1]],$itm[$idx[0]])."</option>\n");
            }
        }
    }
    
    public function addOption($opt_val,$opt_lbl)
    {
        $cmp_val = $this->getGlobal($this->name,$_REQUEST);        
        $opt = $this->add(tag::create('option'))->att('value',$opt_val);
        $opt->add(nvl($opt_lbl,$opt_val));
        if ($cmp_val == $opt_val) {
            $opt->att('selected','selected');
        }
    }
    
    private function buildTree($res)
    {
        $dat = array();
        foreach ($res as $k => $rec) {
            if (empty($rec[2])) {
                $dat[] = $rec;
            } else {
                $this->__grp[$rec[2]][] = $rec;
            }
        }
        $this->buildBranch($dat);
    }

    private function buildBranch($dat, $lev = 0)
    {
        if (empty($dat)) return;
        $len = count($dat)-1;
        $cur_val = $this->getGlobal($this->name, $_REQUEST);
        foreach ($dat as $k => $rec) {
            $val = array();
            foreach ($rec as $j => $v) {
                if (!is_numeric($j)) continue;
                if (count($val) == 2) continue;
                $sta = (empty($lev)) ? '' : '|';
                $end = $len == $k    ? "\\" : "|";
                $val[] = empty($val) ? $v : str_repeat('&nbsp;',$lev*5).$v;
            }
            $sel = ($cur_val == $val[0]) ? ' selected' : '';
            $opt = $this->add(tag::create('option'))
                        ->att('value',$val[0]);
            $opt->add($this->nvl($val[1],$val[0]));
            if ($cur_val == $val[0]) {
                $opt->att('selected','selected');
            }
            //$this->add('<option value="'.$val[0].'"'.$sel.'>'.nvl($val[1],$val[0])."</option>\n");
            if (array_key_exists($val[0],$this->__grp)) {
                $this->buildBranch($this->__grp[$val[0]],$lev+1);
            }
        }
    }
    
    public function setDbHandler($db)
    {
        $this->db = $db;
    }
    
    public function setDatasource($source,$db=null)
    {
        if (empty($db)){
            $trasform = array();
            if (is_array($source)){
                foreach($source as $k => $v) {
                    $trasform[] = [0 => $k, 1 => $v];
                }
                $source = $trasform;
            }
            $this->__dat = $source;
            return $this;
        }
        $this->__par['datasource-sql'] = $source;
        $this->dba = $db;
        return $this;
    }
    
    public function setArray($array)
    {
        $this->__dat = $array;
    }
    
    public function setData($data)
    {
        $this->__dat = $data;
    }
    
    public function setSql($sql, $param=null)
    {
        $this->__par['datasource-sql'] = $sql;
        if ($param) {
            $this->__par['datasource-sql-par'] = $param;
        }
    }
    
    public function setTree($active=true)
    {
        $this->isTree = $active;
    }
}
