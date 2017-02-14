<?php
namespace Osynapsy\Ocl\Component;

use Osynapsy\Core\Lib\Tag;

/**
 * Description of ListView
 *
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class ListSortable extends Component
{    	
    private $rootKey = '[--ROOT--]';

	public function __construct($id)
    {
        parent::__construct('div',$id);
        $this->requireCss('/__assets/osynapsy/Ocl/ListSortable/style.css');
        $this->requireJs('/__assets/osynapsy/Lib/jquery-sortable-0.9.13/jquery-sortable.js');        
		$this->requireJs('/__assets/osynapsy/Ocl/ListSortable/script.js');        
        $this->att('class','osy-listsortable');
        $this->par('record-add','1');
		$this->par('command-add-label','+ Aggiungi');
        $this->par('add_position','header');
        $this->par('num_row',0);
        $this->par('list_height',false);
        $this->par('cols_width',false);        
    }

    protected function __build_extra__()
    {						
		$this->buildHead();
		$this->buildBody();				
    }
	
	protected function savePosition()
	{		
		$i = 10;
		foreach($_REQUEST[$this->id] as $k => $v){
			$this->__db->exec_cmd($sql,array($i,$v));
			$i += 10;
		}
	}
	
	protected function buildHead()
	{
        if ($this->get_par('height')) {
          $this->att('style','height : '.$this->par('height').'px; overflow:auto;');
        }       
	}
	
	protected function buildBody($rootKey=null){
		$ul = $this->add(new Tag('ul'));		
		if (is_null($rootKey)){
			$rootKey = $this->rootKey;
			//$ul = $this->add(new Tag('ul'));
			$ul->att('class','osy-listsortable-body');
		} else {
			//$ul = new Tag('ul');
	 	    $ul->att('data-parent',$rootKey)
			   ->att('class','osy-listsortable-leaf');
		}		
        if (!array_key_exists($rootKey,$this->data)) {
			return '';
		}
        $i = 0;
        foreach ($this->data[$rootKey] as $kr => $row) {
            $li = $ul->add(new Tag('li'));
            $li->att('class','row clearfix');
            $cnt = $li->add(new Tag('div'))->att('class','cnt clearfix osy-listsortable-item');
            if ($kr == 0) {
               $nc = 0;
               foreach ($row as $kr => $v) {
                    if ($kr[0] != '_') {
                        $nc++;
                    }
               }
               $wdt = ($nc > 0 ? floor(75 / $nc) : '75') . '%';
            }
            $form = $j = 0;				
            foreach($row as $k => $v) {						
                $print = false;
                switch($k[0]) {
                    case '_':
                        $par = explode(',',$k);
                        switch($par[0]) {
                           case '_col':
                                       $li->att('class',$v,true);
                                       break;                                   
                           case '_form':
                                        if (!empty($v)) $li->att('data-form',$v);
                                        break;
                           case '_html':
                                        $print = true;
                                        break;
                        }
                        break;
                    default:                                
                        $print = true;
                        break;
                }
                if ($print) {
                    $cnt->add("<div class=\"cell\" style=\"width: {$wdt}\">$v</div>");
                    $j++;
                    $i++;
                }
            }
            if ($this->get_par('form-related') && !is_null($pk)) {
                $cnt->add('<div class="cmd"><span class="btn_edit">Modifica</span></div>');
            }	           
        }
		return $ul;
	}
	
    public function setSql($db, $sql, $par = array())
    {
        $rs =  $db->execQuery($sql, $par, 'ASSOC');        
		$this->par('num_row',count($rs));

        foreach($rs as $rec) {
            if(array_key_exists('_parent',$rec) && !empty($rec['_parent'])) {
				  $this->data[$rec['_parent']][] = $rec;
			} else {
                  $this->data[$this->rootKey][] = $rec;
            }
        }
    }		
}
