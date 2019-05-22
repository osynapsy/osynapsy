<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Component;
use Osynapsy\Html\Tag;

class DataGrid2 extends Component
{      
    private $columns = [];   
    private $emptyMessage = 'No data found';
    private $pagination;
    private $showHeader = true;
    private $title;
    private $rowWidth = 12;
    
    public function __construct($name)
    {
        parent::__construct('div', $name);
        $this->setClass('bcl-datagrid');
        $this->requireCss('Bcl/DataGrid/style.css');
        $this->requireJs('Bcl/DataGrid/script.js');
    }
    
    /**
     * Internal method to build component      
     */
    public function __build_extra__()
    {
        //If datagrid has pager get data from it.
        if (!empty($this->pagination)) {
            try {
                $this->setData($this->pagination->loadData(null, true));
            } catch (\Exception $e) {
                $this->printError($e->getMessage());
            }
        } 
        //If Datagrid has title append and show it.
        if (!empty($this->title)) {
            $this->add($this->buildTitle($this->title));
        }
        //If showHeader === true show datagrid columns.
        if ($this->showHeader) {
            $this->add($this->buildColumnHead());
        }
        //Append Body to datagrid container.
        $this->add($this->buildBody());
        //If datagrid has pager append to foot and show it.
        if (!empty($this->pagination)) {
            $this->add($this->buildPagination());
        }        
    }
    
    private function printError($error)
    {
        $this->setData([['error' => str_replace(PHP_EOL,'<br>',$error)]]);
        $this->columns = [];
        $this->addColumn('Error', 'error', 'col-lg-12');
    }
    
    /**
     * Internal method for build a Datagrid column head.
     * 
     * @return Tag
     */
    private function buildColumnHead()
    {
        $tr = new Tag('div');
        $tr->att('class', 'row bcl-datagrid-thead hidden-xs');
        $orderByFields = $this->pagination ? explode(',', $this->pagination->getOrderBy()) : null;
        foreach(array_keys($this->columns) as $rawLabel) {
            $th = $this->columns[$rawLabel]->buildTh($orderByFields);
            if (empty($th)) {
                continue;
            }
            $tr->add($th);
        }
        return $tr;
    }
    
    /**
     * Internal metod for build empty message.
     * 
     * @param type $body
     * @return type
     */
    private function buildEmptyMessage($body)
    {
        $body->add(
            '<div class="row"><div class="col-lg-12 text-center">'.$this->emptyMessage.'</div></div>'
        );
        return $body;
    }
    
    /**
     * Internal method for build Datagrid body.
     * 
     * @return Tag
     */
    private function buildBody()
    {
        $body = new Tag('div');
        $body->att('class','bcl-datagrid-body');        
        if (empty($this->data)) {
            return $this->buildEmptyMessage($body);
        }        
        if ($this->rowWidth === 12) {
            foreach ($this->data as $recIdx => $rec) {            
                $body->add($this->buildBodyRow($rec));            
            }
            return $body;
        }
        $rowClass =  'bcl-datagrid-body-row col-lg-'.$this->rowWidth;        
        foreach ($this->data as $recIdx => $rec) {            
            if (($recIdx) % (12 / $this->rowWidth) === 0) {
                $row = $body->add(new Tag('div', null, 'row'));
            }
            $row->add($this->buildBodyRow($rec, $rowClass));
        }        
        return $body;
    }
    
    /**
     * Internal method for build a Datagrid row
     * 
     * @param type $row
     * @return Tag
     */
    private function buildBodyRow($record, $class = 'row bcl-datagrid-body-row')
    {
        $tr = new Tag('div', null, $class);        
        foreach ($this->columns as $column) {
            $tr->add($column->buildTd($tr, $record));
        }
        if (!empty($record['_url_detail'])) {
            $tr->att('data-url-detail', $record['_url_detail']);
        }
        return $tr;
    }
    
    /**
     * Build Datagrid pagination
     *      
     * @return Tag
     */
    private function buildPagination()
    {        
        $row = new Tag('div', null, 'row bcl-datagrid-pagination');
        if (empty($this->pagination)) {
            return $row;
        }
        $row->add(new Tag('div', null, 'col-lg-2'))            
            ->add($this->pagination->getPageDimensionsCombo());
        $row->add(new Tag('div', null, 'col-lg-4 col-lg-offset-2 offset-lg-2 text-center'))
             ->add('<label class="" style="margin-top: 30px;">'.$this->pagination->getInfo().'</label>');
        //if ($this->pagination->getStatistic('pageTotal') > 1) {
            $row->add(new Tag('div', null, 'col-lg-4 text-right'))
                ->add($this->pagination)
                ->setClass('mt-4')->setPosition('end');
        //}
        return $row;
    }
            
    /**
     * Build Datagrid title
     *      
     * @return Tag
     */
    private function buildTitle()
    {        
        $tr = new Tag('div', null, 'row bcl-datagrid-title');
        $tr->add(new Tag('div', null, 'col-lg-12'))           
           ->add($this->title);
        return $tr;
    }
               
    /**
     * Add a data column view
     * 
     * @param type $label of column (show)
     * @param type $field name of array data field to show
     * @param type $class css to apply column
     * @param type $type type of data (necessary for formatting value)
     * @param callable $function for manipulate data value
     * @return $this
     */
    public function addColumn(
        $label, 
        $field,
        $class = '',
        $type = 'string',
        callable $function = null,
        $fieldOrderBy = null
    ){
        $column = new DataGrid2Column(
            $label,
            $field,
            $class,
            $type,
            $function,
            $fieldOrderBy
        );
        $column->setParent($this->id);
        $this->columns[$label] = $column;
        return $column;
        //$this->addColumn($label, $field, $class, $type, $function, $fieldOrderBy);
    }
        
    /**
     * return pager object
     * 
     * @return Pagination object
     */
    public function getPagination()
    {
        return $this->pagination;        
    }
    
    /**
     * Hide Header
     * 
     * @return $this;
     */
    public function hideHeader()
    {
        $this->showHeader = false;
        return $this;
    }
    
    /**
     * Set array of columns rule
     * 
     * @param type $columns
     * @return $this
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
        return $this;
    }
    
    /**
     * Set message to show when no data found.
     * 
     * @param type $message
     * @return $this
     */  
    public function setEmptyMessage($message)
    {
        $this->emptyMessage = $message;
        return $this;
    }
    
    /**
     * Set width of row in bootstrap unit grid (max width = 12)
     * 
     * @param int $width
     */
    public function setRowWidth($width)
    {
        $this->rowWidth = $width;
        return $this;
    }
    
    /**
     * Set a pagination object
     *      * 
     * @param type $db Handler db connection
     * @param string $sqlQuery Sql query
     * @param array $sqlParameters Parameters of sql query
     * @param integer $pageDimension Page dimension (in row)
     */
    public function setPagination($db, $sqlQuery, $sqlParameters, $pageDimension = 10)
    {
        $this->pagination = new Pagination(
            $this->id.(strpos($this->id, '_') ? '_pagination' : 'Pagination'),
            empty($pageDimension) ? 10 : $pageDimension
        );
        $this->pagination->setSql($db, $sqlQuery, $sqlParameters);
        $this->pagination->setParentComponent($this->id);        
        return $this->pagination;
    }
    
    /**
     * Method for set table and rows borders visible
     * 
     * return void;
     */
    public function setBorderOn()
    {
        $this->setClass('bcl-datagrid-border-on');
    }
    
    /**
     * Set title to show on top of datagrid
     * 
     * @param type $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
}
