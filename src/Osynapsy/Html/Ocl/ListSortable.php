<?php
namespace Osynapsy\Html\Ocl;

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;
/**
 * Description of ListView
 *
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class ListSortable extends Component
{    	
    private $rootKey = '[--ROOT--]';
    private $head;
    private $columnFunction = array();
    private $emptyMessage;
    
	public function __construct($id)
    {
        parent::__construct('div', $id);
        
        $this->requireCss('/__assets/osynapsy/Ocl/ListSortable/style.css');
        //$this->requireJs('/__assets/osynapsy/Lib/jquery-sortable-0.9.13/jquery-sortable.js');  
        $this->requireJs('/__assets/osynapsy/Lib/html5-sortable/jquery-sortable.js');
		$this->requireJs('/__assets/osynapsy/Ocl/ListSortable/script.js');
        $this->add('<input type="hidden" id="'.$id.'_order" name="'.$id.'">');
        $this->att('class','osy-listsortable')
             ->att('data-action','sortList')
             ->att('data-action-parameters','#'.$id.'_order');
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
            $this->add(
                $this->head
            );
        }
		$this->add(
            $this->buildBody()
        );        
    }
	
	protected function buildHead()
	{
        if ($this->get_par('height')) {
            $this->att('style','height : '.$this->par('height').'px; overflow:auto;');
        }       
	}
	
	protected function buildBody($rootKey=null)
    {
		$ul = new Tag('ul');
		if (is_null($rootKey)){
			$rootKey = $this->rootKey;			
			$ul->att('class','osy-listsortable-body');
            if (empty($this->data[$rootKey])) {                
                $this->buildEmptyMessage();                
            }
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
            if (!empty($row['_id']) && !empty($this->data[$row['_id']])) {
                $branchBody = $this->buildBody($row['_id']);
                $li->add($branchBody);
            }            
        }        
		return $ul;
	}
	
    private function buildEmptyMessage()
    {        
        if (empty($this->emptyMessage)) {
            return;
        }        
        $this->add('<div class="osy-listsortable-emptymessage">'.$this->emptyMessage.'</div>');
    }
    
    private function buildRow($rec, $container)
    {
        foreach($rec as $fieldName => $fieldValue) {		           
            $container->add(
                $this->buildCell(
                    $fieldName,
                    $fieldValue,
                    $container
                )
            );
        }
    }
    
    private function buildCell($fieldName, $fieldValue, $container)
    {
        $print = false;
        if (array_key_exists($fieldName, $this->columnFunction)) {
            $fieldValue = $this->columnFunction[$fieldName]($fieldValue, $fieldName);
        }
        switch($fieldName[0]) {
            case '_':
                $par = explode(',',$fieldName);
                switch($par[0]) {                          
                    case '_id':
                        $container->att('data-id', $fieldValue);
                        return;
                    case '_html':
                        $print = true;
                        break;
                    case '_cmd':
                        return '<div class="cmd">'.$fieldValue.'</div>';
                    case '_detail':
                        return '<div class="cmd"><a href="'.$fieldValue.'" class="btn btn-default save-history"><span class="glyphicon glyphicon-pencil"></span></a></div>';                        
                    
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
    
    public function addColumnFunction($column, callable $function)
    {
        $this->columnFunction[$column] = $function;
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
        $this->att('data-action', $action);        
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
    
    public function setEmptyMessage($msg)
    {
        $this->emptyMessage = $msg;
    }
}