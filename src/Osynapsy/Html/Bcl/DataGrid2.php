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
        $this->att('class','bcl-datagrid');
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
            $this->setData($this->pagination->loadData());
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
            
    /**
     * Internal method for build a Datagrid column head.
     * 
     * @return Tag
     */
    private function buildColumnHead()
    {
        $tr = new Tag('div');
        $tr->att('class', 'row bcl-datagrid-thead');
        foreach($this->columns as $label => $properties) {
            if (empty($label)) {
                continue;
            } elseif ($label[0] == '_') {
                continue;
            }
            $tr->add(new Tag('div'))
               ->att('class', $properties['class'].' hidden-xs bcl-datagrid-th')               
               ->add($label);
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
                $body->add($this->buildRow($rec));            
            }
            return $body;
        }
        $rowClass =  'col-lg-'.$this->rowWidth;        
        foreach ($this->data as $recIdx => $rec) {            
            if (($recIdx) % (12 / $this->rowWidth) === 0) {
                $row = $body->add(new Tag('div', null, 'row'));
            }
            $row->add($this->buildRow($rec, $rowClass));
        }        
        return $body;
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
        $row->add(new Tag('div', null, 'col-lg-6 col-lg-offset-4 text-right'))
            ->add($this->pagination);
        return $row;
    }
    
    /**
     * Internal method for build a Datagrid row
     * 
     * @param type $row
     * @return Tag
     */
    private function buildRow($row, $class = 'row')
    {
        $tr = new Tag('div', null, $class);        
        foreach ($this->columns as $properties) {
            if (is_callable($properties['field'])) {
                $properties['function'] = $properties['field'];
                $value = null;
            } else {
                $value = array_key_exists($properties['field'], $row) ? 
                         $row[$properties['field']] : 
                         '<label class="label label-warning">No data found</label>';            
            }
            $cell = $tr->add(new Tag('div', null, 'bcl-datagrid-td'));            
            $cell->add(
                $this->valueFormatting($value, $cell, $properties, $row, $tr)
            );
        }
        if (!empty($row['_url_detail'])) {
            $tr->att('data-url-detail', $row['_url_detail']);
        }
        return $tr;
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
     * Format a value of cell for correct visualization
     * 
     * @param string $value to format.
     * @param object $cell container of value
     * @param type $properties 
     * @param type $rec record which contains value.
     * @param type $tr row container object
     * @return string
     */
    private function valueFormatting($value, &$cell, $properties, $rec, &$tr)
    {        
        switch($properties['type']) {            
            case 'money':
                $value = number_format($value, 2, ',', '.');
                $properties['class'] .= ' text-right';
                break;
            case 'commands':
                $properties['class'] .= ' cmd-row';
                break;
        }        
        if (!empty($properties['function'])) {
            $value = $properties['function']($value, $cell, $rec, $tr);    
        }
        if (!empty($properties['class'])) {
            $cell->att('class', $properties['class'], true);
        }
        return ($value != 0 && empty($value)) ? '&nbsp;' : $value;
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
    public function addColumn($label, $field, $class = '', $type = 'string',callable $function = null)
    {
        $this->columns[$label] = [
            'field' => $field,
            'class' => $class,
            'type' => $type,
            'function' => $function
        ];
        return $this;
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
