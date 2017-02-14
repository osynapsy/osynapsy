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
    private $head;
    
	public function __construct($id)
    {
        parent::__construct('div', $id);
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
		if ($this->head) {
            $this->add($this->head);
        }
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
			$ul->att('class','osy-listsortable-body');
		} else {			
	 	    $ul->att('data-parent',$rootKey)
			   ->att('class','osy-listsortable-leaf');
		}		
        if (!array_key_exists($rootKey,$this->data)) {
			return '';
		}        
        foreach ($this->data[$rootKey] as $kr => $row) {
            $li = $ul->add(new Tag('li'));
            $li->att('class','row clearfix');
            $container = $li->add(new Tag('div'))
                            ->att('class','cnt clearfix osy-listsortable-item');
            if ($kr == 0) {
               $nc = 0;
               foreach ($row as $kr => $v) {
                    if ($kr[0] != '_') {
                        $nc++;
                    }
               }
               $wdt = ($nc > 0 ? floor(75 / $nc) : '75') . '%';
            }
            $this->buildRow($row, $container);
            if ($this->get_par('form-related') && !is_null($pk)) {
                $cnt->add();
            }	           
        }
		return $ul;
	}
	
    private function buildRow($rec, $container)
    {
        foreach($rec as $fieldName => $fieldValue) {						           
            $container->add(
                $this->buildCell(
                    $fieldName,
                    $fieldValue
                )
            );
        }
    }
    
    private function buildCell($fieldName, $fieldValue)
    {
        $print = false;        
        switch($fieldName[0]) {
            case '_':
                $par = explode(',',$fieldName);
                switch($par[0]) {                          
                    case '_html':
                        $print = true;
                        break;
                    case '_cmd':
                        return '<div class="cmd"><a href="'.$fieldValue.'" class="btn btn-default"><span class="glyphicon glyphicon-pencil"></span></a></div>';                        
                }
                break;
            default:                                
                $print = true;
                break;
        }
        if ($print) {
            return "<div class=\"cell\" style=\"width: {$wdt}\">$fieldValue</div>";
        }
    }
    
    public function getHead()
    {
        if ($this->head){
            return $this->head;
        }
        $this->head = new Tag('div');        
        $this->head->att('class','clearfix ocl-listsortable-head');
        return $this->head;
    }
    
    public function setAction($action, $parameters = null)
    {
        $this->att('data-action', $action)
             ->att('data-action-parameters', $parameters);        
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
