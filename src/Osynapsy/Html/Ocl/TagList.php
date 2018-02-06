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

use Osynapsy\Html\Component;

class TagList extends Component
{
    public function __construct($id=null)
    {
        parent::__construct('div',$id);
        $this->class = 'osy-taglist';
        $this->requireJs('Ocl/TagList/script.js');
    }
    
	protected function __build_extra__()
    {
		$this->add(new hidden_box($this->id));
		if ($_REQUEST['ajax'] == $this->id){
			$this->__ajax_exec__();
		}
        $build_tag_from_datasource = false;
		if ($this->getParameter('database-save-parameters')){
			$this->att('class','osy-taglist-onextab',true);
            $build_tag_from_datasource = true;
		}
		$ul = $this->add(tag::create('ul'));
		if ($sql = $this->getParameter('datasource-sql'))
		{
			$sql = kkernel::replacevariable($sql);
            $res = kkernel::$dba->exec_query($sql,null,'NUM');
			$datalist = $this->add(tag::create('datalist'));
			$datalist->att('id',$this->id.'_data');
        	foreach($res as $k => $rec)
        	{
            	if ($rec[2] == 1){
					$ul->add('<li class="osy-taglist-entry" tid="'.$rec[0].'"><span class="osy-taglist-entry-text">'.$rec[1].'</span><a href="#" class="osy-taglist-entry-remove">remove</a></li>');
				}
				$datalist->add(tag::create('option'))->add($rec[1]);
        	}
		}
		
        if(!$build_tag_from_datasource && !empty($_REQUEST[$this->id]))
		{
			$item_list = explode(',',$_REQUEST[$this->id]);
			foreach($item_list as $k => $v){
				$ul->add('<li class="osy-taglist-entry" pos="'.$k.'"><span class="osy-taglist-entry-text">'.$v.'</span><a href="#" class="osy-taglist-entry-remove">remove</a></li>');
			}
		}
        $txt = $ul->add(tag::create('li'))
		   		  ->att('class','listbuilder-entry-text')
           		  ->add(tag::create('input'))
		   		  ->att('name',$this->id.'_add')
		   		  ->att('type','text')
		   		  ->att('class','add osy-taglist-input');
		if (isset($datalist)){
			$txt->att('list',$this->id.'_data');
		}
		$ul->add('<br style="clear: both">');
		
    }

	protected function __ajax_exec__(){

		if (!array_key_exists('pkey',$_REQUEST)) die('PKey empty');
		$tag_lst = array();
		if ($sql = $this->getParameter('datasource-sql'))
		{
			$res = kkernel::$dba->exec_query($sql,null,'NUM');
			foreach($res as $k => $rec){
				$tag_lst[$rec[1]] = $rec[0];
			}
		}
		if ($raw_par = $this->getParameter('database-save-parameters'))
		{
		    $sql_par = array();
			$raw_par = explode(',',$raw_par);
			$table = array_shift($raw_par);
			$tagfld = array_pop($raw_par);
			if (count($raw_par) != count($_REQUEST['pkey'])){
				die("Number of fkey don't match number di fkey filed. ".print_r($raw_par,true).print_r($_REQUEST['pkey'],true));
			}
			$i = 0;
			foreach($_REQUEST['pkey'] as $k => $pkey){
				$sql_par[$raw_par[$i]] = $pkey;
				$i++;
			}
		} else {
			die('Parameter database-save-parameters empty!');
		}
		$rsp = null;
		if (!array_key_exists($_REQUEST['tag'],$tag_lst)){
			die("Tag {$_REQUEST['tag']} don't exists in datalist");//.print_r($_REQUEST['tag'])."\n".print_r($tag_lst,true));
		} 
		//Prendo il tag ID e lo aggiungo ai parametri sql per l'inserimento in tabella.
		$sql_par[$tagfld] = $tag_lst[$_REQUEST['tag']];
		switch($_POST['ajax-cmd'])
		{
		  case 'add':
		                /*
						$tid = kkernel::$dba->exec_unique("SELECT id as tid 
		                                               FROM {$table}
		                                            WHERE {$fkey} = ? 
		                                              AND t = ?",array(UID,$_POST['tag']));
		                if (empty($tid)){
		                    kkernel::$dba->exec_cmd("INSERT INTO tbl_adb_tag 
		                                        (id_ana,tag,dat_ins) 
		                                       VALUES 
		                                        (?,?,NOW())",array(UID,$_POST['tag']));
		                    $tid = kkernel::$dba->lastInsertId();
		                } 
						*/
		                kkernel::$dba->insert($table,$sql_par);
		                $rsp = 'OK';
		                break;
		  case 'del':
	                   	kkernel::$dba->delete($table,$sql_par);
		                $rsp = 'OK';
		                break;
		}
		die($rsp);
	}
}