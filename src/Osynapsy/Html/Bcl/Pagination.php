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
use Osynapsy\Html\Ocl\HiddenBox;
use Osynapsy\Html\Tag;

/**
 * Description of Pagination
 *
 * @author Pietro Celeste
 */
class Pagination extends Component
{
    private $columns = [];
    private $entity = 'Record';
    protected $data = [];
    protected $errors = [];
    protected $pageDimensionPalceholder = '- Dimensione pagina -';
    private $db;
    private $filters = [];
    private $fields = [];
    private $loaded = false;
    private $par;
    private $sql;
    private $orderBy = null;
    private $parentComponent;
    private $position = 'center';
    private $statistics = [
        //Dimension of the pag in row;
        'pageDimension' => 10,
        'pageTotal' => 1,
        'pageCurrent' => 1,
        'rowsTotal' => 0
    ];
    private $pageDimensions = [
        1 => ['10', '10 righe'],
        2 => ['20', '20 righe'],
        5 => ['50', '50 righe'],
        10 => ['100', '100 righe'],
        20 => ['200', '200 righe']
    ];
    /**
     * Costructor of pager component.
     *
     * @param type $id Identify of component
     * @param type $dim Page dimension in number of row
     * @param type $tag Tag of container
     * @param type $infiniteContainer Enable infinite scroll?
     */
    public function __construct($id, $pageDimension = 10, $tag = 'div', $infiniteContainer = false)
    {
        parent::__construct($tag, $id);
        if (!empty($infiniteContainer)) {
            $this->setInfiniteScroll($infiniteContainer);
        }
        $this->requireJs('Bcl/Pagination/script.js');
        $this->setClass('BclPagination');
        if ($tag == 'form') {
            $this->att('method','post');
        }
        $this->setPageDimension($pageDimension);
        $this->setOrder(filter_input(\INPUT_POST, $this->id.'OrderBy'));
    }

    public function __build_extra__()
    {
        if (!$this->loaded) {
            $this->loadData;
        }
        $this->add(new HiddenBox($this->id))
             ->setClass('BclPaginationCurrentPage');
        $this->add(new HiddenBox($this->id.'OrderBy'))
             ->setClass('BclPaginationOrderBy');
        foreach($this->fields as $field) {
            $this->add(new HiddenBox($field, $field.'_hidden'));
        }
        $ul = $this->add(new Tag('ul', null, 'pagination justify-content-'.$this->position));
        $liFirst = $ul->add(new Tag('li', null, 'page-item'));
        if ($this->statistics['pageCurrent'] < 2) {
            $liFirst->att('class','disabled');
        }
        $liFirst->add(new Tag('a', null, 'page-link'))
                ->att('data-value','first')
                ->att('href','#')
                ->add('&laquo;');
        $dim = min(7, $this->statistics['pageTotal']);
        $app = floor($dim / 2);
        $pageMin = max(1, $this->statistics['pageCurrent'] - $app);
        $pageMax = max($dim, min($this->statistics['pageCurrent'] + $app, $this->statistics['pageTotal']));
        $pageMin = min($pageMin, $this->statistics['pageTotal'] - $dim + 1);
        for ($i = $pageMin; $i <= $pageMax; $i++) {
            $liCurrent = $ul->add(new Tag('li', null, 'page-item'));
            if ($i == $this->statistics['pageCurrent']) {
                $liCurrent->att('class','active', true);
            }
            $liCurrent->add(new Tag('a', null, 'page-link'))
                      ->att('data-value',$i)
                      ->att('href','#')
                      ->add($i);
        }
        $liLast = $ul->add(new Tag('li', null, 'page-item'));
        if ($this->statistics['pageCurrent'] >= $this->statistics['pageTotal']) {
            $liLast->att('class','disabled');
        }
        $liLast->add(new Tag('a', null, 'page-link'))
               ->att('href','#')
               ->att('data-value','last')
               ->add('&raquo;');
    }

    public function addField($field)
    {
        $this->fields[] = $field;
    }

    public function addFilter($field, $value = null)
    {
        $this->filters[$field] = $value;
    }

    private function buildMySqlQuery($where)
    {
        $sql = "SELECT a.* FROM ({$this->sql}) a {$where} ";
        if (!empty($_REQUEST[$this->id.'_order'])) {
            $sql .= ' ORDER BY '.str_replace(array('][','[',']'),array(',','',''),$_REQUEST[$this->id.'_order']);
        } elseif ($this->orderBy) {
            $sql .= "\nORDER BY {$this->orderBy}";
        }
        if (empty($this->statistics['pageDimension'])) {
            return $sql;
        }
        $startFrom = ($this->statistics['pageCurrent'] - 1) * $this->statistics['pageDimension'];
        $startFrom = max(0, $startFrom);

        $sql .= "\nLIMIT ".$startFrom." , ".$this->statistics['pageDimension'];
        return $sql;
    }

    private function buildPgSqlQuery($where)
    {
        $sql = "SELECT a.* FROM ({$this->sql}) a {$where} ";
        if (!empty($_REQUEST[$this->id.'_order'])) {
            $sql .= ' ORDER BY '.str_replace(array('][','[',']'),array(',','',''),$_REQUEST[$this->id.'_order']);
        } elseif ($this->orderBy) {
            $sql .= "\nORDER BY {$this->orderBy}";
        }
        if (empty($this->statistics['pageDimension'])) {
            return $sql;
        }
        $startFrom = ($this->statistics['pageCurrent'] - 1) * $this->statistics['pageDimension'];
        $startFrom = max(0, $startFrom);
        $sql .= "\nLIMIT ".$this->statistics['pageDimension']." OFFSET ".$startFrom;
        return $sql;
    }

    private function buildOracleQuery($where)
    {
        $sql = "SELECT a.*
                FROM (
                    SELECT b.*,rownum as \"_rnum\"
                    FROM (
                        SELECT a.*
                        FROM ($this->sql) a
                        ".(empty($where) ? '' : $where)."
                        ".(!empty($_REQUEST[$this->id.'_order']) ? ' ORDER BY '.str_replace(array('][','[',']'),array(',','',''),$_REQUEST[$this->id.'_order']) : '')."
                    ) b
                ) a ";
        if (empty($this->statistics['pageDimension'])) {
            return $sql;
        }
        $startFrom = (($this->statistics['pageCurrent'] - 1) * $this->statistics['pageDimension']) + 1 ;
        $endTo = ($this->statistics['pageCurrent'] * $this->statistics['pageDimension']);
        $sql .=  "WHERE \"_rnum\" BETWEEN $startFrom AND $endTo";
        return $sql;
    }

    private function buildFilter()
    {
        if (empty($this->filters)) {
            return;
        }
        $filter = array();
        $i = 0;
        foreach ($this->filters as $field => $value) {
            if (is_null($value)) {
                $filter[] = $field;
                continue;
            }
            $filter[] = "$field = ".($this->db->getType() == 'oracle' ? ':'.$i : '?');
            $this->par[] = $value;
            $i++;
        }
        return " WHERE " .implode(' AND ',$filter);
    }

    private function calcPage($requestPage)
    {
        $this->statistics['pageCurrent'] = max(1,(int) $requestPage);
        if ($this->statistics['rowsTotal'] == 0 || empty($this->statistics['pageDimension'])) {
            return;
        }
        $this->statistics['pageTotal'] = ceil($this->statistics['rowsTotal'] / $this->statistics['pageDimension']);
        $this->att(
            'data-page-max',
            max($this->statistics['pageTotal'],1)
        );
        switch ($requestPage) {
            case 'first':
                $this->statistics['pageCurrent'] = 1;
                break;
            case 'last' :
                $this->statistics['pageCurrent'] = $this->statistics['pageTotal'];
                break;
            case 'prev':
                if ($this->statistics['pageCurrent'] > 1){
                    $this->statistics['pageCurrent']--;
                }
                break;
            case 'next':
                if ($this->statistics['pageCurrent'] < $this->statistics['pageTotal']) {
                    $this->statistics['pageCurrent']++;
                }
                break;
            default:
                $this->statistics['pageCurrent'] = min($this->statistics['pageCurrent'], $this->statistics['pageTotal']);
                break;
        }
    }

    public function getPageDimensionsCombo()
    {
        $Combo = new ComboBox($this->id.(strpos($this->id, '_') ? '_page_dimension' : 'PageDimension'));
        $Combo->setPlaceholder($this->pageDimensionPalceholder);
        $Combo->att('onchange',"Osynapsy.refreshComponents(['{$this->parentComponent}'])")
              ->att('style','margin-top: 20px;')
              ->setArray($this->pageDimensions);
        return $Combo;
    }

    public function getInfo()
    {
        $end = min($this->getStatistic('pageCurrent') * $this->getStatistic('pageDimension'), $this->getStatistic('rowsTotal'));
        $start = ($this->getStatistic('pageCurrent') - 1) * $this->getStatistic('pageDimension') + 1;
        $info = 'da ';
        $info .= $start;
        $info .= ' a ';
        $info .= $end;
        $info .= ' di ';
        $info .= $this->getStatistic('rowsTotal');
        $info .= ' ';
        $info .= $this->entity;

        return $info;
    }

    public function getOrderBy()
    {
        return $this->orderBy;
    }

    public function getTotal($key)
    {
        return $this->getStatistic('total'.ucfirst($key));
    }

    public function getStatistic($key = null)
    {
        return array_key_exists($key, $this->statistics) ? $this->statistics[$key] : null;
    }

    public function loadData($requestPage = null, $exceptionOnError = false)
    {
        if (empty($this->sql)) {
            return array();
        }
        if (is_null($requestPage) && filter_input(\INPUT_POST, $this->id)) {
            $requestPage = filter_input(\INPUT_POST, $this->id);
        }
        $where = $this->buildFilter();

        $count = "SELECT COUNT(*) FROM (\n{$this->sql}\n) a " . $where;

        try {
            $this->statistics['rowsTotal'] = $this->db->execUnique($count, $this->par);
            $this->att('data-total-rows', $this->statistics['rowsTotal']);
        } catch(\Exception $e) {
            $this->errors[] = '<pre>'.$count."\n".$e->getMessage().'</pre>';
            if ($exceptionOnError) {
                throw new \Exception('<span class="text-danger">'.$e->getMessage().'</span>'.PHP_EOL.PHP_EOL.$this->sql);
            }
            return [];
        }

        $this->calcPage($requestPage);

        switch ($this->db->getType()) {
            case 'oracle':
                $sql = $this->buildOracleQuery($where);
                break;
            case 'pgsql':
                $sql = $this->buildPgSqlQuery($where);
                break;
            default:
                $sql = $this->buildMySqlQuery($where);
                break;
        }
        //Eseguo la query
        try {
            $this->data = $this->db->execQuery($sql, $this->par, 'ASSOC');
        } catch (\Exception $e) {
            die($sql.$e->getMessage());
        }
        //die(print_r($this->data,true));
        //Salvo le colonne in un option
        $this->columns = $this->db->getColumns();
        return empty($this->data) ? array() : $this->data;
    }

    public function setInfiniteScroll($container)
    {
        $this->requireJs('Lib/imagesLoaded-4.1.1/imagesloaded.js');
        $this->requireJs('Lib/wookmark-2.1.2/wookmark.js');
        $this->att('class','infinitescroll',true)->att('style','display: none');
        if ($container[0] != '#' ||  $container[0] != '#') {
            $container = '#'.$container;
        }
        return $this->att('data-container',$container);
    }

    public function setOrder($field)
    {
        if (empty($this->orderBy)) {
            $this->orderBy = str_replace(['][', '[', ']'], [',' ,'' ,''], $field);
        }
        return $this;
    }

    public function setPageDimension($pageDimension)
    {
        if (!empty($_REQUEST[$this->id.'PageDimension'])) {
            $this->statistics['pageDimension'] = $_REQUEST[$this->id.'PageDimension'];
        } elseif (!empty($_REQUEST[$this->id.'_page_dimension'])) {
            $this->statistics['pageDimension'] = $_REQUEST[$this->id.'_page_dimension'];
        } else {
            $this->statistics['pageDimension'] = $pageDimension;
        }
        if ($pageDimension === 10) {
            return;
        }
        foreach(array_keys($this->pageDimensions) as $key) {
            $dimension = $pageDimension * $key;
            $this->pageDimensions[$key] = [$dimension, "{$dimension} righe"];
        }
    }

    public function setPageDimensionPlaceholder($label)
    {
        $this->pageDimensionPalceholder = $label;
    }

    public function setParentComponent($componentId)
    {
        $this->parentComponent = $componentId;
        $this->att('data-parent', $componentId);
        return $this;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function setSql($db, $cmd, array $par = array())
    {
        $this->db = $db;
        $this->sql = $cmd;
        $this->par = $par;
        return $this;
    }

    public function getStatistics()
    {
        return $this->page;
    }

    public function getErrors()
    {
        return implode(PHP_EOL, $this->errors);
    }
}
